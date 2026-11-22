<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Creates or updates the administrator account from ADMIN_EMAIL / ADMIN_PASSWORD in .env
 * (config/bakr.php 'admin'). Safe to re-run: it updates by e-mail. Role is set here only.
 *
 *   php artisan db:seed --class=AdminAccountSeeder
 */
class AdminAccountSeeder extends Seeder
{
    public const MIN_PASSWORD_LENGTH = 12;

    public function run(): void
    {
        $email = trim((string) config('bakr.admin.email'));
        $password = (string) config('bakr.admin.password');

        if ($email === '' || $password === '') {
            $this->command?->warn('Skipped: set ADMIN_EMAIL and ADMIN_PASSWORD in .env, then run php artisan config:clear.');

            return;
        }
        if (strlen($password) < self::MIN_PASSWORD_LENGTH) {
            $this->command?->warn('Skipped: ADMIN_PASSWORD must be at least '.self::MIN_PASSWORD_LENGTH.' characters.');

            return;
        }

        $user = User::where('email', $email)->first();
        // Never turn a student, scholar or provider account into an admin.
        if ($user && $user->role !== 'admin') {
            $this->command?->warn("Skipped: {$email} already belongs to a non-admin account.");

            return;
        }

        $user ??= new User;
        $user->name = (string) config('bakr.admin.name');
        $user->email = $email;
        $user->password = Hash::make($password);
        $user->role = 'admin';
        $user->status = 'active';
        $user->save();

        $this->command?->info('Admin account ready.');
    }
}
