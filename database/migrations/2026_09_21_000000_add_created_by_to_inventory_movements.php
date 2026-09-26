<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_movements', function (Blueprint $table): void {
            if (! Schema::hasColumn('inventory_movements', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('product_id')->constrained('users')->nullOnDelete();
            }
        });
        Schema::table('inventory_movements', function (Blueprint $table): void {
            try {
                $table->index('created_by');
            } catch (Throwable $e) {
            }
        });
    }

    public function down(): void
    {
        Schema::table('inventory_movements', function (Blueprint $table): void {
            try {
                $table->dropIndex(['created_by']);
            } catch (Throwable $e) {
            }
            try {
                $table->dropConstrainedForeignId('created_by');
            } catch (Throwable $e) {
                if (Schema::hasColumn('inventory_movements', 'created_by')) {
                    $table->dropColumn('created_by');
                }
            }
        });
    }
};
