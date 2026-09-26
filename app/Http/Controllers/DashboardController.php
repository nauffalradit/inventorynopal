<?php

namespace App\Http\Controllers;

use App\Models\InventoryMovement;
use App\Models\NotificationMessage;
use App\Models\Product;
use App\Models\Report;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class DashboardController extends Controller
{
    // Flush semua varian cache dashboard (admin/staff/per-user). Dipakai semua controller & jobs.
    public static function flushDashboard(?int $uid = null): void
    {
        Cache::forget('dashboard:stats');
        Cache::forget('dashboard:stats:admin');
        Cache::forget('dashboard:stats:staff');
        Cache::forget('dashboard:recentMovements');
        if ($uid) {
            Cache::forget("dashboard:recentMovements:user:$uid");
        }
    }

    public function __invoke(): View
    {
        $isAdmin = auth()->user()?->isAdmin() ?? false;

        // Keep All: cache agregasi 60s — key dibedakan per role agar staff tidak intip angka admin
        $statsKey = $isAdmin ? 'dashboard:stats:admin' : 'dashboard:stats:staff';
        $stats = Cache::remember($statsKey, 60, function () use ($isAdmin): array {
            $base = [
                'productCount' => Product::count(),
                'totalStock' => Product::sum('stock'),
                'lowStockCount' => Product::whereColumn('stock', '<=', 'minimum_stock')->count(),
            ];
            // pendingReports hanya dihitung untuk admin (laporan = administratif)
            $base['pendingReports'] = $isAdmin ? Report::where('status', 'pending')->count() : 0;

            return $base;
        });

        // recentMovements: admin lihat semua + siapa pencatat; staff hanya mutasi miliknya
        if ($isAdmin) {
            $recentMovements = Cache::remember('dashboard:recentMovements', 30, fn () => InventoryMovement::with(['product:id,name', 'creator:id,name'])->latest()->limit(8)->get()
            );
        } else {
            $uid = auth()->id();
            $recentMovements = Cache::remember("dashboard:recentMovements:user:$uid", 30, fn () => InventoryMovement::with(['product:id,name', 'creator:id,name'])->where('created_by', $uid)->latest()->limit(8)->get()
            );
        }

        $recentNotifications = Cache::remember('dashboard:recentNotifications', 30, fn () => NotificationMessage::latest()->limit(5)->get()
        );

        return view('dashboard', [
            'productCount' => $stats['productCount'],
            'totalStock' => $stats['totalStock'],
            'lowStockCount' => $stats['lowStockCount'],
            'pendingReports' => $stats['pendingReports'],
            'recentMovements' => $recentMovements,
            'recentNotifications' => $recentNotifications,
            'isAdminView' => $isAdmin,
        ]);
    }
}
