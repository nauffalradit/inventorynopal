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
        ];
    } // <-- Ini penutup fungsi casts()

    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
    ];
} 