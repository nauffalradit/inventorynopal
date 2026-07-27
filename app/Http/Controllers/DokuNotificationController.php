<?php

namespace App\Http\Controllers;

use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Services\DokuCheckout;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DokuNotificationController extends Controller
{
    public function __invoke(Request $request, DokuCheckout $doku): JsonResponse
    {
        $target = '/payments/doku/notification';
        abort_unless($doku->notificationIsValid($request->getContent(), $request->headers->all(), $target), 403, 'Invalid DOKU signature.');
        $payload = $request->json()->all();
        $invoice = data_get($payload, 'order.invoice_number') ?? data_get($payload, 'order.invoice');
        $gatewayStatus = strtoupper((string) (data_get($payload, 'transaction.status') ?? data_get($payload, 'transaction_status') ?? 'PENDING'));
        $payment = Payment::where('invoice_number', $invoice)->firstOrFail();
        $status = match ($gatewayStatus) { 'SUCCESS', 'PAID' => 'paid', 'EXPIRED' => 'expired', 'FAILED', 'CANCELLED' => 'failed', default => 'pending' };

        DB::transaction(function () use ($payment, $status, $payload): void {
            $payment->update(['status' => $status, 'gateway_response' => $payload, 'paid_at' => $status === 'paid' ? now() : null]);
            $order = Order::lockForUpdate()->with('items')->findOrFail($payment->order_id);
            if ($status !== 'paid' || $order->status === 'paid') return;
            foreach ($order->items as $item) {
                $product = Product::lockForUpdate()->findOrFail($item->product_id);
                abort_if($product->stock < $item->quantity, 409, 'Insufficient stock.');
                $product->decrement('stock', $item->quantity);
                InventoryMovement::create(['product_id' => $product->id, 'type' => 'out', 'quantity' => -$item->quantity, 'balance_after' => $product->fresh()->stock, 'notes' => "Penjualan {$order->number}"]);
            }
            $order->update(['status' => 'paid', 'paid_at' => now()]);
        });
        return response()->json(['status' => 'ok']);
    }
}
