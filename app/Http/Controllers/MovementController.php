<?php

namespace App\Http\Controllers;

use App\Jobs\SendInventoryNotification;
use App\Models\InventoryMovement;
use App\Models\NotificationMessage;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class MovementController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'type' => ['required', 'in:in,out,adjustment'],
            'quantity' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        // STAFF boleh in/out, adjustment hanya ADMIN (Security)
        if ($data['type'] === 'adjustment') {
            Gate::authorize('admin');
        }

        DB::transaction(function () use ($data): void {
            $product = Product::lockForUpdate()->findOrFail($data['product_id']);
            $signedQuantity = match ($data['type']) {
                'out' => -$data['quantity'],
                default => $data['quantity'],
            };

            $product->stock = max(0, $product->stock + $signedQuantity);
            $product->save();

            InventoryMovement::create([
                ...$data,
                'created_by' => auth()->id(),
                'quantity' => $signedQuantity,
                'balance_after' => $product->stock,
            ]);
        });

        DashboardController::flushDashboard(auth()->id());
        for ($i = 1; $i <= 20; $i++) {
            Cache::forget("products:index:page:$i");
        }

        // Peringatan 1x per produk per hari saat stok menyentuh batas minimum.
        $product = Product::find($data['product_id']);
        if ($product && $product->stock <= $product->minimum_stock) {
            $subject = "Stok menipis: {$product->sku}";
            $already = NotificationMessage::where('subject', $subject)->whereDate('created_at', today())->exists();
            if (! $already) {
                $notification = NotificationMessage::create([
                    'channel' => 'internal',
                    'recipient' => 'admin',
                    'subject' => $subject,
                    'message' => "{$product->name} tersisa {$product->stock} {$product->unit} (minimum {$product->minimum_stock}). Segera restock.",
                    'status' => 'pending',
                ]);
                SendInventoryNotification::dispatch($notification)->onQueue('notifications');
                Cache::forget('dashboard:recentNotifications');
                for ($i = 1; $i <= 20; $i++) {
                    Cache::forget("notifications:index:page:$i");
                }
            }
        }

        return back()->with('status', 'Mutasi stok berhasil dicatat.');
    }
}
