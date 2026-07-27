<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MovementController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\DokuNotificationController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::view('login', 'auth.login')->name('login');
    Route::get('auth/google', [App\Http\Controllers\Auth\GoogleController::class, 'redirectToGoogle'])->name('auth.google.redirect');
    Route::get('auth/google/callback', [App\Http\Controllers\Auth\GoogleController::class, 'handleGoogleCallback'])->name('auth.google.callback');
});

Route::post('payments/doku/notification', DokuNotificationController::class)->name('doku.notification');

Route::middleware('auth')->group(function (): void {
    Route::post('logout', [App\Http\Controllers\Auth\GoogleController::class, 'logout'])->name('logout');

    Route::get('/', DashboardController::class)->name('dashboard');

    Route::resource('products', ProductController::class)->except(['show']);
    Route::post('movements', [MovementController::class, 'store'])->name('movements.store');
    Route::resource('reports', ReportController::class)->only(['index', 'store', 'show']);
    Route::resource('notifications', NotificationController::class)->only(['index', 'store']);
    Route::resource('orders', OrderController::class)->only(['index', 'create', 'store', 'show', 'destroy']);
    Route::post('orders/{order}/pay', [OrderController::class, 'pay'])->name('orders.pay');
    Route::post('orders/{order}/refresh-payment', [OrderController::class, 'refreshPayment'])->name('orders.refresh-payment');
});
