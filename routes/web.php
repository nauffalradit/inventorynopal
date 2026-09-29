<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DokuNotificationController;
use App\Http\Controllers\MovementController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::view('login', 'auth.login')->name('login');
    Route::get('auth/google', [GoogleController::class, 'redirectToGoogle'])->name('auth.google.redirect');
    Route::get('auth/google/callback', [GoogleController::class, 'handleGoogleCallback'])->name('auth.google.callback');
});

Route::post('payments/doku/notification', DokuNotificationController::class)->name('doku.notification');

Route::middleware(['auth', 'active'])->group(function (): void {
    Route::post('logout', [GoogleController::class, 'logout'])->name('logout');

    Route::get('/', DashboardController::class)->name('dashboard');

    // STAFF operasional: lihat barang, lihat stok, catat masuk/keluar, transaksi own
    Route::get('products', [ProductController::class, 'index'])->name('products.index');
    Route::post('movements', [MovementController::class, 'store'])->name('movements.store');
    Route::resource('orders', OrderController::class)->only(['index', 'create', 'store', 'show', 'destroy']);
    Route::post('orders/{order}/pay', [OrderController::class, 'pay'])->name('orders.pay');
    Route::post('orders/{order}/refresh-payment', [OrderController::class, 'refreshPayment'])->name('orders.refresh-payment');
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/{report}', [ReportController::class, 'show'])->name('reports.show');
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');

    // ADMIN only: kelola barang, laporan, notifikasi, user
    Route::middleware('admin')->group(function (): void {
        Route::get('products/create', [ProductController::class, 'create'])->name('products.create');
        Route::post('products', [ProductController::class, 'store'])->name('products.store');
        Route::get('products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('products/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::delete('products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

        Route::post('reports', [ReportController::class, 'store'])->name('reports.store');
        Route::delete('reports/{report}', [ReportController::class, 'destroy'])->name('reports.destroy');
        Route::post('notifications', [NotificationController::class, 'store'])->name('notifications.store');

        Route::get('admin/users', [UserController::class, 'index'])->name('admin.users.index');
        Route::patch('admin/users/{user}/role', [UserController::class, 'updateRole'])->name('admin.users.role');
        Route::patch('admin/users/{user}/toggle', [UserController::class, 'toggleActive'])->name('admin.users.toggle');
    });
});
