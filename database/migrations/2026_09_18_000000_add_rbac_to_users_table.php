<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'role')) {
                $table->string('role', 20)->default('staff')->after('avatar');
            }
            if (! Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('role');
            }
        });

        // index terpisah agar index 2026_09_04 tidak bentrok
        Schema::table('users', function (Blueprint $table): void {
            try {
                $table->index('role');
            } catch (Throwable $e) {
            }
            try {
                $table->index('is_active');
            } catch (Throwable $e) {
            }
        });

        // backfill existing users: jika email ada di ADMIN_EMAILS env, jadikan admin (best effort, tidak fatal jika env kosong)
        try {
            $adminEmails = array_filter(array_map(fn ($e) => strtolower(trim($e)), explode(',', (string) env('ADMIN_EMAILS', ''))));
            if ($adminEmails) {
                User::whereIn(DB::raw('LOWER(email)'), $adminEmails)->update(['role' => 'admin']);
            }
        } catch (Throwable $e) {
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            try {
                $table->dropIndex(['role']);
            } catch (Throwable $e) {
            }
            try {
                $table->dropIndex(['is_active']);
            } catch (Throwable $e) {
            }
            if (Schema::hasColumn('users', 'is_active')) {
                $table->dropColumn('is_active');
            }
            if (Schema::hasColumn('users', 'role')) {
                $table->dropColumn('role');
            }
        });
    }
};
