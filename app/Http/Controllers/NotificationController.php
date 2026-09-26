<?php

namespace App\Http\Controllers;

use App\Jobs\SendInventoryNotification;
use App\Models\NotificationMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(): View
    {
        $page = (int) request()->input('page', 1);
        $notifications = Cache::remember("notifications:index:page:$page", 30, fn () => NotificationMessage::latest()->paginate(15));

        return view('notifications.index', compact('notifications'));
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('admin');
        $data = $request->validate([
            'channel' => ['required', 'in:email,whatsapp,internal'],
            'recipient' => ['required', 'string', 'max:160'],
            'subject' => ['required', 'string', 'max:160'],
            'message' => ['required', 'string', 'max:1000'],
        ]);

        $notification = NotificationMessage::create([
            ...$data,
            'status' => 'pending',
        ]);

        SendInventoryNotification::dispatch($notification)->onQueue('notifications');
        for ($i = 1; $i <= 20; $i++) {
            Cache::forget("notifications:index:page:$i");
        }

        return to_route('notifications.index')->with('status', 'Notifikasi masuk ke antrian komunikasi.');
    }
}
