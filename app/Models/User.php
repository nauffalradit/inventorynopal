<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $hidden = [
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'avatar',
        'role',
        'is_active',
    ];

    // RBAC hybrid: DB role + ADMIN_EMAILS fallback
    public function isAdmin(): bool
    {
        if (($this->role ?? 'staff') === 'admin') {
            return true;
        }
        $admins = config('services.admin_emails', []);

        return in_array(strtolower((string) $this->email), array_map('strtolower', $admins), true);
    }

    public static function isAdminEmail(string $email): bool
    {
        $admins = config('services.admin_emails', []);

        return in_array(strtolower(trim($email)), array_map('strtolower', $admins), true);
    }

    public function isActive(): bool
    {
        return (bool) ($this->is_active ?? true);
    }
}
