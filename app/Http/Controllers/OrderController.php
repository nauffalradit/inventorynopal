<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\InventoryMovement;
use App\Services\DokuCheckout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function index() { return view('orders.index', ['orders' => Order::with('payments')->latest()->paginate(15)]); }
    public function create() { return view('orders.create', ['products' => Product::where('stock', '>', 0)->orderBy('name')->get()]); }
    public function store(Request $request)
    {
        $data = $request->validate(['customer_name' => ['required','string','max:120'], 'customer_email' => ['required','email'], 'items' => ['required','array','min:1'], 'items.*.product_id' => ['required','exists:products,id'], 'items.*.quantity' => ['required','integer','min:1']]);
        $order = DB::transaction(function () use ($data) {
            $order = Order::create(['user_id' => auth()->id(), 'number' => 'ORD'.now()->format('YmdHis').Str::upper(Str::random(4)), 'customer_name' => $data['customer_name'], 'customer_email' => $data['customer_email'], 'total_amount' => 0]);
            $total = 0;
            foreach ($data['items'] as $item) { $product = Product::lockForUpdate()->findOrFail($item['product_id']); abort_if($product->stock < $item['quantity'], 422, "Stok {$product->name} tidak cukup."); $subtotal = $product->price * $item['quantity']; $total += $subtotal; $order->items()->create(['product_id'=>$product->id,'product_name'=>$product->name,'quantity'=>$item['quantity'],'unit_price'=>$product->price,'subtotal'=>$subtotal]); }
            $order->update(['total_amount' => $total]); return $order;
        });
        return to_route('orders.show', $order)->with('status', 'Order dibuat. Lanjutkan ke DOKU Checkout.');
    }
    public function show(Order $order) { $order->load('items','payments'); return view('orders.show', compact('order')); }
    public function pay(Order $order, DokuCheckout $doku)
    {
        abort_unless($order->status === 'pending', 422);
        try { $result = $doku->create($order); abort_if(blank($result['url']), 502, 'DOKU tidak mengirim payment URL.'); Payment::updateOrCreate(['invoice_number'=>$order->number], ['order_id'=>$order->id,'request_id'=>$result['request_id'],'amount'=>$order->total_amount,'status'=>'pending','checkout_url'=>$result['url'],'gateway_response'=>$result['response']]); return to_route('orders.show', $order)->with('status', 'Checkout DOKU dibuat. Buka checkout, lalu gunakan simulator sandbox untuk menyelesaikan pembayaran.'); }
        catch (\Throwable $e) { report($e); return back()->with('error', 'Checkout DOKU gagal dibuat. Periksa DOKU_CLIENT_ID dan DOKU_SECRET_KEY.'); }
    }
    public function destroy(Order $order)
    {
        abort_if($order->status === 'paid', 422, 'Order berhasil tidak dapat dihapus.');
        $order->delete();
        return to_route('orders.index')->with('status', 'Order dihapus.');
    }
    public function refreshPayment(Order $order, DokuCheckout $doku)
    {
        try {
            $response = $doku->status($order->number);
            $status = strtoupper((string) (data_get($response, 'transaction.status') ?? data_get($response, 'response.transaction.status') ?? 'PENDING'));
            if (!in_array($status, ['SUCCESS', 'PAID'], true)) return back()->with('status', 'Pembayaran masih menunggu di DOKU.');
            DB::transaction(function () use ($order, $response): void {
                $order = Order::lockForUpdate()->with('items')->findOrFail($order->id);
                if ($order->status === 'paid') return;
                foreach ($order->items as $item) { $product = Product::lockForUpdate()->findOrFail($item->product_id); abort_if($product->stock < $item->quantity, 409, 'Stok tidak mencukupi.'); $product->decrement('stock', $item->quantity); InventoryMovement::create(['product_id'=>$product->id, 'type'=>'out', 'quantity'=>-$item->quantity, 'balance_after'=>$product->fresh()->stock, 'notes'=>"Penjualan {$order->number}"]); }
                $order->payments()->latest()->first()?->update(['status'=>'paid', 'paid_at'=>now(), 'gateway_response'=>$response]);
                $order->update(['status'=>'paid', 'paid_at'=>now()]);
            });
            return to_route('orders.show', $order)->with('status', 'Pembayaran berhasil dikonfirmasi dari DOKU.');
        } catch (\Throwable $e) { report($e); return back()->with('error', 'Status DOKU belum dapat diperbarui. Coba beberapa saat lagi.'); }
    }
}
