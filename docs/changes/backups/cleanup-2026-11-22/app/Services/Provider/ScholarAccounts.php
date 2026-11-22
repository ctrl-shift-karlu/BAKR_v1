<?php

namespace App\Services\Provider;

use App\Mail\ScholarAccountCreated;
use App\Models\Scholar;
use App\Models\User;
use App\Support\MailTime;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

/**
 * The scholar's BAKR login, created at the moment the provider adds the scholar.
 * Scholar accounts (role "scholar": monitoring portal at /scholar, signed in at /scholarLogin)
 * are separate from student accounts (role "student": matching, /studentLogin). One e-mail
 * holds one account, so (owner's decisions 2026-11-18):
 *
 *  - New e-mail      → a SCHOLAR account (role/status set here, never from input) with a random
 *                      temporary password and must_change_password = true. The password is e-mailed
 *                      once and never stored in plain text, shown to the provider or logged by BAKR.
 *                      NOTE: with MAIL_MAILER=log Laravel writes the whole e-mail, password included,
 *                      to storage/logs/laravel.log. Use smtp anywhere real.
 *  - Scholar e-mail  → that scholar account gets the extra scholarship; a notice, not a password.
 *  - Student e-mail  → that student account BECOMES a scholar account (same password); a notice
 *                      tells them to use the Scholar login from now on. Their matching answers stay
 *                      in the database but are no longer shown.
 *  - Any other role  → refused (a provider or staff e-mail can never become a scholar login).
 *
 * Call forEmail() inside the caller's DB transaction: a conversion is rolled back with the scholar row.
 * The scholar is sent to "Change your password" until they do (EnsurePasswordChanged).
 */
class ScholarAccounts
{
    public const PASSWORD_LENGTH = 12;

    /** Roles whose e-mail can be used for a scholar login (a student account is converted). */
    public const USABLE_ROLES = ['scholar', 'student'];

    /**
     * Finds, converts or creates the scholar account for this e-mail.
     *
     * @return array{user: ?User, password: ?string, error: ?string, converted: bool}
     *                                                                                password only for a new account
     */
    public function forEmail(string $email, string $name): array
    {
        $email = Str::lower(trim($email));
        $existing = User::query()->whereRaw('LOWER(email) = ?', [$email])->first();

        if ($existing) {
            if (! in_array($existing->role, self::USABLE_ROLES, true)) {
                return ['user' => null, 'password' => null, 'error' => "{$email} belongs to a BAKR provider or staff account. Use the scholar's own e-mail.", 'converted' => false];
            }
            $converted = $existing->role === 'student';
            if ($converted) {
                $existing->forceFill(['role' => 'scholar'])->save();   // server-side only
            }

            return ['user' => $existing, 'password' => null, 'error' => null, 'converted' => $converted];
        }

        $password = $this->temporaryPassword();
        $user = new User;
        $user->name = $name;
        $user->email = $email;
        $user->password = Hash::make($password);
        // Role, status and the forced change are set server-side only.
        $user->role = 'scholar';
        $user->status = 'active';
        $user->must_change_password = true;
        $user->save();

        return ['user' => $user, 'password' => $password, 'error' => null, 'converted' => false];
    }

    /**
     * E-mails the login details (or, for an existing account, a "you were added" notice; $converted:
     * "your student account is now a scholar account").
     * Records credentials_sent_at on success; on failure it stays null so the provider can resend.
     */
    public function send(Scholar $scholar, ?string $password, bool $converted = false): bool
    {
        $scholar->loadMissing(['user', 'scholarship']);

        try {
            MailTime::extend();   // an Excel import e-mails every new login in one request
            Mail::to($scholar->user->email)->send(new ScholarAccountCreated($scholar, $password, $converted));
            // MAIL_MAILER=log only writes the e-mail to storage/logs/laravel.log: nobody receives it,
            // so it is not recorded as sent (the provider sees "Login not sent" and can resend later).
            if (self::mailIsTestMode()) {
                return false;
            }
            $scholar->forceFill(['credentials_sent_at' => now()])->save();

            return true;
        } catch (Throwable $e) {
            report($e);   // the exception never contains the password; the mailable is not logged

            return false;
        }
    }

    /** True when e-mails are only written to the log (MAIL_MAILER=log), not delivered. */
    public static function mailIsTestMode(): bool
    {
        return config('mail.default') === 'log';
    }

    /** New temporary password + e-mail, while the scholar still has not changed the first one. */
    public function resend(Scholar $scholar): bool
    {
        $scholar->loadMissing('user');
        if (! $scholar->canResendLogin()) {
            return false;
        }

        $password = $this->temporaryPassword();
        $scholar->user->forceFill(['password' => Hash::make($password), 'must_change_password' => true])->save();
        $scholar->forceFill(['credentials_sent_at' => null])->save();

        return $this->send($scholar, $password);
    }

    /** 12 characters, letters and numbers only (easy to type from an e-mail), at least one of each. */
    private function temporaryPassword(): string
    {
        do {
            $p = Str::password(self::PASSWORD_LENGTH, letters: true, numbers: true, symbols: false, spaces: false);
        } while (! preg_match('/[A-Za-z]/', $p) || ! preg_match('/\d/', $p));

        return $p;
    }
}
