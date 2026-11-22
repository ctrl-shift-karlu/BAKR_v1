<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Creates or updates the 5 Provider Portal accounts from config/providers.php,
 * which reads PROVIDER_<KEY>_EMAIL / PROVIDER_<KEY>_PASSWORD from .env.
 * Safe to re-run: it updates by e-mail. Role and provider_key are set here only.
 */
class ProviderAccountSeeder extends Seeder
{
    public const MIN_PASSWORD_LENGTH = 12;

    public function run(): void
    {
        foreach (config('providers.accounts', []) as $key => $provider) {
            $email = trim((string) ($provider['email'] ?? ''));
            $password = (string) ($provider['password'] ?? '');
            $upper = strtoupper($key);

            if ($email === '' || $password === '') {
                $this->warn("Skipped {$key}: set PROVIDER_{$upper}_EMAIL and PROVIDER_{$upper}_PASSWORD in .env.");

                continue;
            }

            if (strlen($password) < self::MIN_PASSWORD_LENGTH) {
                $this->warn("Skipped {$key}: PROVIDER_{$upper}_PASSWORD must be at least ".self::MIN_PASSWORD_LENGTH.' characters.');

                continue;
            }

            $user = User::where('email', $email)->first();

            // Never turn an existing student/staff account into a provider account.
            if ($user && $user->role !== 'provider') {
                $this->warn("Skipped {$key}: {$email} already belongs to a non-provider account.");

                continue;
            }

            $user ??= new User;
            $user->name = $provider['name'];
            $user->email = $email;
            $user->password = Hash::make($password);
            $user->role = 'provider';
            $user->provider_key = $key;
            $user->status = 'active';
            $user->save();

            $this->info("Provider account ready: {$key}");
        }

        // A provider login whose key is no longer in config/providers.php (e.g. the old "coscho" account,
        // now part of CHED) can never sign in; keep it deactivated so the data matches what login accepts.
        $stale = User::where('role', 'provider')
            ->where('status', 'active')
            ->whereNotIn('provider_key', array_keys(config('providers.accounts', [])))
            ->update(['status' => 'inactive']);
        if ($stale > 0) {
            $this->info("Deactivated {$stale} provider account(s) that no longer match config/providers.php.");
        }
    }

    private function warn(string $message): void
    {
        $this->command?->warn($message);
    }

    private function info(string $message): void
    {
        $this->command?->info($message);
    }
}
