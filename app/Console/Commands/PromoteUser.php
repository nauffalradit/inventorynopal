<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class PromoteUser extends Command
{
    protected $signature = 'user:promote {email : Email user} {--role=admin : Role admin|staff}';

    protected $description = 'Ubah role user (admin/staff) — mekanisme emergency, tetap ada meski ADMIN_EMAILS fallback aktif';

    public function handle(): int
    {
        $email = $this->argument('email');
        $role = $this->option('role');
        if (! in_array($role, ['admin', 'staff'], true)) {
            $this->error('Role harus admin atau staff.');

            return 1;
        }
        $user = User::where('email', $email)->first();
        if (! $user) {
            $this->error("User $email tidak ditemukan.");

            return 1;
        }
        $user->update(['role' => $role]);
        $this->info("User $email role diubah menjadi $role (isAdmin: ".($user->fresh()->isAdmin() ? 'ya' : 'tidak').').');

        return 0;
    }
}
