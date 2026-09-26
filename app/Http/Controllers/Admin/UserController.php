<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $role = request()->input('role');
        $status = request()->input('status');
        $users = User::when($role, fn ($q) => $q->where('role', $role))
            ->when($status === 'aktif', fn ($q) => $q->where('is_active', true))
            ->when($status === 'nonaktif', fn ($q) => $q->where('is_active', false))
            ->orderBy('name')->paginate(15)->withQueryString();

        return view('admin.users.index', compact('users', 'role', 'status'));
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'role' => ['required', 'in:admin,staff'],
        ]);

        // STAFF tidak boleh mengubah role dirinya sendiri menjadi ADMIN (payload tampering)
        if ($user->id === auth()->id() && $data['role'] !== $user->role) {
            abort(403, 'Tidak dapat mengubah role diri sendiri.');
        }

        // Jangan promote user nonaktif menjadi admin (aktifkan dulu)
        if ($data['role'] === 'admin' && ! $user->isActive()) {
            return back()->with('error', 'Aktifkan akun '.$user->name.' dulu sebelum promote menjadi admin.');
        }

        // cegah admin terakhir ter-demote (optional, tapi aman)
        if ($user->role === 'admin' && $data['role'] === 'staff') {
            $adminCount = User::where('role', 'admin')->count();
            // hitung fallback ADMIN_EMAILS juga sebagai admin — jika hanya 1 admin DB dan ada fallback, tetap izinkan demote
            if ($adminCount <= 1 && ! User::isAdminEmail($user->email)) {
                return back()->with('error', 'Tidak dapat demote admin terakhir.');
            }
        }

        $user->update(['role' => $data['role']]);

        return back()->with('status', 'Role '.$user->name.' diubah menjadi '.$data['role'].'.');
    }

    public function toggleActive(User $user): RedirectResponse
    {
        // tidak boleh nonaktifkan diri sendiri
        if ($user->id === auth()->id()) {
            abort(403, 'Tidak dapat menonaktifkan akun sendiri.');
        }

        // jangan nonaktifkan fallback ADMIN_EMAILS (emergency access) — hapus dari env dulu jika memang mau
        if ($user->is_active && User::isAdminEmail($user->email)) {
            return back()->with('error', 'Akun ini fallback ADMIN_EMAILS (emergency access). Hapus dari .env dulu jika memang ingin menonaktifkan.');
        }

        $user->update(['is_active' => ! $user->is_active]);

        return back()->with('status', $user->name.' '.($user->is_active ? 'diaktifkan' : 'dinonaktifkan').'.');
    }
}
