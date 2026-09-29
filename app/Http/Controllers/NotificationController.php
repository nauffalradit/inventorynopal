<?php

namespace App\Http\Controllers;

use App\Jobs\SendInventoryNotification;
use App\Models\NotificationMessage;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(): View
    {
        $page = (int) request()->input('page', 1);
        $notifications = Cache::remember("notifications:index:page:$page", 30, fn () => NotificationMessage::latest()->paginate(15));
        $users = User::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'email', 'role']);
        $sender = config('mail.from.address').' ('.config('mail.from.name').')';

        return view('notifications.index', compact('notifications', 'users', 'sender'));
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('admin');
        $data = $request->validate([
            'channel' => ['required', 'in:email,whatsapp,internal'],
            'subject' => ['required', 'string', 'max:160'],
            'message' => ['required', 'string', 'max:1000'],
            'scope' => ['nullable', 'in:specific,all,admin,staff'],
        ]);

        // Expand penerima di server (jangan percaya daftar dari browser).
        $scope = $data['scope'] ?? 'specific';
        if (in_array($scope, ['all', 'admin', 'staff'], true)) {
            $emails = User::query()->where('is_active', true)
                ->when($scope !== 'all', fn ($q) => $q->where('role', $scope))
                ->orderBy('id')->pluck('email')->all();
            abort_if($emails === [], 422, 'Tidak ada akun aktif pada cakupan ini.');
        } else {
            $picked = $request->validate([
                'recipients' => ['required', 'array', 'max:50'],
                'recipients.*' => ['required', 'email', 'max:160', Rule::exists('users', 'email')->where('is_active', true)],
            ]);
            $emails = array_values(array_unique($picked['recipients']));
        }

        foreach ($emails as $email) {
            $notification = NotificationMessage::create([
                'channel' => $data['channel'],
                'recipient' => $email,
                'subject' => $data['subject'],
                'message' => $data['message'],
                'status' => 'pending',
            ]);
            SendInventoryNotification::dispatch($notification)->onQueue('notifications');
        }
        for ($i = 1; $i <= 20; $i++) {
            Cache::forget("notifications:index:page:$i");
        }

        return to_route('notifications.index')->with('status', 'Notifikasi untuk '.count($emails).' penerima masuk ke antrian komunikasi.');
    }
}
