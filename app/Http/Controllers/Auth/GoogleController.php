<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleController extends Controller
{
    // Mengarahkan user ke halaman login Google
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    // Menerima data kembali dari Google
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            // Mencari apakah user sudah terdaftar di database kita
            $user = User::where('google_id', $googleUser->id)
                ->orWhere('email', $googleUser->email)
                ->first();

            if ($user) {
                if (empty($user->google_id)) {
                    $user->update(['google_id' => $googleUser->id, 'avatar' => $googleUser->getAvatar()]);
                }
                if (! $user->isActive()) {
                    return redirect('/login')->with('error', 'Akun dinonaktifkan. Hubungi admin.');
                }
                // auto-promote jika email ada di ADMIN_EMAILS fallback
                if (! $user->isAdmin() && User::isAdminEmail($user->email)) {
                    // hybrid: DB role tetap staff tapi isAdmin() true via env; tidak perlu update DB
                }
                Auth::login($user);
            } else {
                // Fase 1: kunci pendaftaran (default open — tidak merusak demo)
                $mode = config('services.registration', 'open');
                if ($mode === 'invite') {
                    return redirect('/login')->with('error', 'Pendaftaran ditutup. Minta admin untuk mengundang akun Anda.');
                }
                if ($mode === 'domain') {
                    $allowed = config('services.allowed_domains', []);
                    $domain = strtolower(substr(strrchr($googleUser->email, '@') ?: '', 1));
                    $ok = $allowed === [] ? true : in_array($domain, array_map(fn ($d) => ltrim($d, '@'), $allowed), true)
                        || in_array('@'.$domain, $allowed, true)
                        || in_array(strtolower($googleUser->email), $allowed, true);
                    if (! $ok) {
                        return redirect('/login')->with('error', 'Email tidak diizinkan. Gunakan email kantor.');
                    }
                }
                $isAdminEmail = User::isAdminEmail($googleUser->email);
                $newUser = User::create([
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'google_id' => $googleUser->id,
                    'avatar' => $googleUser->getAvatar(),
                    'role' => $isAdminEmail ? 'admin' : 'staff',
                    'is_active' => true,
                ]);

                Auth::login($newUser);
            }

            request()->session()->regenerate();

            return redirect()->intended(route('dashboard'));

        } catch (Throwable $e) {
            Log::error('Google login failed.', ['exception' => $e]);

            return redirect('/login')->with('error', 'Gagal login menggunakan Google.');
        }
    }

    // Logout user dari aplikasi
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
