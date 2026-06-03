<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MovementController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', DashboardController::class)->name('dashboard');

Route::resource('products', ProductController::class)->except(['show']);
Route::post('movements', [MovementController::class, 'store'])->name('movements.store');
Route::resource('reports', ReportController::class)->only(['index', 'store', 'show']);
Route::resource('notifications', NotificationController::class)->only(['index', 'store']);
