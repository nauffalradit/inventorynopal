<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Exception;

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
                // Jika user sudah terdaftar, perbarui google_id-nya jika kosong
                if (empty($user->google_id)) {
                    $user->update(['google_id' => $googleUser->id]);
                }
                Auth::login($user);
            } else {
                // Jika belum terdaftar, buat akun baru otomatis di database
                $newUser = User::create([
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'google_id' => $googleUser->id,
                ]);

                Auth::login($newUser);
            }

            request()->session()->regenerate();

            // Dashboard aplikasi berada pada route bernama "dashboard" (/).
            return redirect()->intended(route('dashboard'));

        } catch (Exception $e) {
            Log::error('Google login failed.', ['exception' => $e]);
            return redirect('/login')->with('error', 'Gagal login menggunakan Google.');
        }
    }

    // Logout user dari aplikasi
    public function logout()
    {
        Auth::logout();
        return redirect('/login');
    }
}
