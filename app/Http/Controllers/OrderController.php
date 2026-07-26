<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
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
        try { $result = $doku->create($order); abort_if(blank($result['url']), 502, 'DOKU tidak mengirim payment URL.'); Payment::updateOrCreate(['invoice_number'=>$order->number], ['order_id'=>$order->id,'request_id'=>$result['request_id'],'amount'=>$order->total_amount,'status'=>'pending','checkout_url'=>$result['url'],'gateway_response'=>$result['response']]); return redirect()->away($result['url']); }
        catch (\Throwable $e) { report($e); return back()->with('error', 'Checkout DOKU gagal dibuat. Periksa DOKU_CLIENT_ID dan DOKU_SECRET_KEY.'); }
    }
}
