<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // products — percepat Dashboard count/sum/whereColumn & Order::where(stock>0)
        Schema::table('products', function (Blueprint $table): void {
            $table->index('stock');
            $table->index('minimum_stock');
            $table->index('created_at');
            $table->index('category');
        });

        // inventory_movements — Dashboard recentMovements latest()
        Schema::table('inventory_movements', function (Blueprint $table): void {
            $table->index('created_at');
            $table->index(['product_id', 'created_at']);
        });

        // reports — Report::latest/paginate + pending count
        Schema::table('reports', function (Blueprint $table): void {
            $table->index('status');
            $table->index('created_at');
            $table->index(['status', 'created_at']);
        });

        // notification_messages — Notification latest/paginate
        Schema::table('notification_messages', function (Blueprint $table): void {
            $table->index('status');
            $table->index('created_at');
            $table->index(['status', 'created_at']);
        });

        // orders — Order::latest/with payments + pendingReports
        Schema::table('orders', function (Blueprint $table): void {
            $table->index('status');
            $table->index('created_at');
            $table->index(['status', 'created_at']);
            $table->index('user_id');
        });

        Schema::table('order_items', function (Blueprint $table): void {
            $table->index('order_id');
            $table->index('product_id');
        });

        Schema::table('payments', function (Blueprint $table): void {
            $table->index('status');
            $table->index('created_at');
            $table->index('order_id');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table): void {
            $table->dropIndex(['status']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['order_id']);
        });
        Schema::table('order_items', function (Blueprint $table): void {
            $table->dropIndex(['order_id']);
            $table->dropIndex(['product_id']);
        });
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropIndex(['status']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['status', 'created_at']);
            $table->dropIndex(['user_id']);
        });
        Schema::table('notification_messages', function (Blueprint $table): void {
            $table->dropIndex(['status']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['status', 'created_at']);
        });
        Schema::table('reports', function (Blueprint $table): void {
            $table->dropIndex(['status']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['status', 'created_at']);
        });
        Schema::table('inventory_movements', function (Blueprint $table): void {
            $table->dropIndex(['created_at']);
            $table->dropIndex(['product_id', 'created_at']);
        });
        Schema::table('products', function (Blueprint $table): void {
            $table->dropIndex(['stock']);
            $table->dropIndex(['minimum_stock']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['category']);
        });
    }
};
