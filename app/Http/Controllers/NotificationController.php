<?php

namespace App\Http\Controllers;

use App\Jobs\SendInventoryNotification;
use App\Models\NotificationMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(): View
    {
        return view('notifications.index', [
            'notifications' => NotificationMessage::latest()->paginate(15),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
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

        return to_route('notifications.index')->with('status', 'Notifikasi masuk ke antrian komunikasi.');
    }
}
