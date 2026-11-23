<?php

namespace App\Services\Provider;

use App\Mail\ScholarEmailChanged;
use App\Mail\ScholarEmailChangeRequested;
use App\Models\Scholar;
use App\Models\User;
use App\Support\MailTime;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

/**
 * The provider changes a scholar's sign-in e-mail (owner, 2026-11-23), e.g. when the scholar lost access to their
 * Gmail. The new address must be proven to WORK before it replaces the old one, so:
 *
 *  1. request()  The provider types the new address. It is checked (valid, not used by another BAKR account or
 *                another scholar of this scholarship) and kept as `pending_email` with a random token (only its
 *                sha256 is stored). A confirmation link is e-mailed to the NEW address. Nothing changes yet: the
 *                scholar still signs in with the current e-mail. If the e-mail cannot be sent, nothing is kept.
 *  2. confirm()  The scholar opens the link (within Scholar::EMAIL_CHANGE_HOURS). Only then the e-mail changes,
 *                on the scholar row, on every row of the same account and on the account itself (role scholar
 *                only; the sign-in follows). A notice goes to the OLD address (if it still works). A login whose
 *                temporary password was never used gets new login details at the new address.
 *  3. cancel()   The provider withdraws a change that is still waiting; the link stops working.
 *
 * Every step works on a Scholar the caller already looked up inside ProviderScope (or by id + token).
 */
class ScholarEmailChange
{
    public function __construct(private ScholarAccounts $accounts) {}

    /**
     * Why $email cannot become this scholar's sign-in e-mail, or null when it can.
     */
    public function problem(Scholar $scholar, string $email): ?string
    {
        $email = Str::lower(trim($email));
        if ($email === Str::lower((string) $scholar->contactEmail())) {
            return 'That is already the scholar\'s e-mail. Type the new address.';
        }
        $owner = User::query()->whereRaw('LOWER(email) = ?', [$email])->first();
        if ($owner && $owner->id !== $scholar->user_id) {
            return "{$email} is already used by another BAKR account. Use an address only this scholar uses.";
        }
        $taken = Scholar::query()->where('scholarship_id', $scholar->scholarship_id)->whereKeyNot($scholar->id)
            ->where(fn ($q) => $q->whereRaw('LOWER(email) = ?', [$email])
                ->orWhereHas('user', fn ($u) => $u->whereRaw('LOWER(email) = ?', [$email])))
            ->exists();

        return $taken ? "Another scholar of {$scholar->scholarship->shortName()} already uses {$email}." : null;
    }

    /**
     * Keeps $email as the pending address and e-mails the confirmation link to it.
     *
     * @return bool true when the link was really sent (false: mail failed or test mode; nothing is kept then)
     */
    public function request(Scholar $scholar, string $email): bool
    {
        $email = Str::lower(trim($email));
        $token = Str::random(48);
        $scholar->forceFill([
            'pending_email' => $email,
            'pending_email_token' => hash('sha256', $token),
            'pending_email_sent_at' => now(),
        ])->save();

        try {
            MailTime::extend();
            Mail::to($email)->send(new ScholarEmailChangeRequested($scholar->loadMissing('scholarship'), route('scholar.email.confirm', [$scholar->id, $token])));
            if (! ScholarAccounts::mailIsTestMode()) {
                return true;
            }
        } catch (Throwable $e) {
            report($e);   // never contains the token in a readable form the provider sees
        }
        $this->cancel($scholar);

        return false;
    }

    /** Withdraws a waiting change: the e-mailed link stops working. */
    public function cancel(Scholar $scholar): void
    {
        $scholar->forceFill(['pending_email' => null, 'pending_email_token' => null, 'pending_email_sent_at' => null])->save();
    }

    /**
     * The scholar opened the link. Returns [state, scholar]:
     *   changed   the e-mail was changed (scholar row, the account's other rows, the account)
     *   invalid   unknown, used, withdrawn or wrong token
     *   expired   the link is older than Scholar::EMAIL_CHANGE_HOURS
     *   taken     meanwhile another account took the address (the change is withdrawn)
     *
     * @return array{0: string, 1: ?Scholar, 2: bool} [state, scholar, login details sent to the new address]
     */
    public function confirm(int $scholarId, string $token): array
    {
        $scholar = Scholar::with(['user', 'scholarship'])->find($scholarId);
        if (! $scholar || ! $scholar->pending_email || ! $scholar->pending_email_token
            || ! hash_equals($scholar->pending_email_token, hash('sha256', $token))) {
            return ['invalid', null, false];
        }
        if (! $scholar->hasPendingEmail()) {
            $this->cancel($scholar);

            return ['expired', $scholar, false];
        }

        $new = $scholar->pending_email;
        if ($this->problem($scholar, $new) !== null) {
            $this->cancel($scholar);

            return ['taken', $scholar, false];
        }

        $old = $scholar->contactEmail();
        DB::transaction(function () use ($scholar, $new) {
            $user = $scholar->user;
            if ($user && $user->role === 'scholar') {
                // The sign-in follows; every scholarship row of this account keeps the same e-mail.
                $user->forceFill(['email' => $new, 'email_verified_at' => now()])->save();
                Scholar::query()->where('user_id', $user->id)->update(['email' => $new]);
            }
            $scholar->forceFill(['email' => $new, 'pending_email' => null, 'pending_email_token' => null, 'pending_email_sent_at' => null])->save();
        });
        $scholar->refresh()->load(['user', 'scholarship']);

        // A temporary password that was never used (perhaps never received at the old address): new login details.
        $loginSent = $scholar->canResendLogin() && $this->accounts->resend($scholar);

        if ($old && Str::lower($old) !== $new) {
            try {
                MailTime::extend();
                Mail::to($old)->send(new ScholarEmailChanged($scholar, $new, $loginSent));
            } catch (Throwable $e) {
                report($e);   // the old address may be the one that stopped working: not a problem
            }
        }

        return ['changed', $scholar, $loginSent];
    }
}
