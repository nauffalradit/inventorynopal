<?php

namespace App\Http\Controllers;

use App\Models\InventoryMovement;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
                'quantity' => $signedQuantity,
                'balance_after' => $product->stock,
            ]);
        });

        return back()->with('status', 'Mutasi stok berhasil dicatat.');
    }
}
