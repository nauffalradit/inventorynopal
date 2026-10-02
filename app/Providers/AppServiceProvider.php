<?php

namespace App\Providers;

use App\Http\Controllers\NotificationController;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::define('admin', fn (User $user) => $user->isAdmin());
        Gate::define('active', fn (User $user) => $user->isActive());

        // Angka bel global: notifikasi sent + belum dibaca milik user login (cache 60 dtk).
        View::composer('layouts.app', function ($view): void {
            $user = auth()->user();
            $view->with('unreadNotifications', $user
                ? NotificationController::unreadCount($user->id, (string) $user->email)
                : 0);
        });
    }
}
