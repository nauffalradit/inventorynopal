<?php

namespace App\Http\Controllers;

use App\Models\InventoryMovement;
use App\Models\NotificationMessage;
use App\Models\Product;
use App\Models\Report;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('dashboard', [
            'productCount' => Product::count(),
            'totalStock' => Product::sum('stock'),
            'lowStockCount' => Product::whereColumn('stock', '<=', 'minimum_stock')->count(),
            'pendingReports' => Report::where('status', 'pending')->count(),
            'recentMovements' => InventoryMovement::with('product')->latest()->limit(8)->get(),
            'recentNotifications' => NotificationMessage::latest()->limit(5)->get(),
        ]);
    }
}
