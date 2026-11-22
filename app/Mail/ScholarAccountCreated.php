<?php

namespace App\Mail;

use App\Models\Scholar;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Sent when a provider adds a scholar (ScholarAccounts). With $password: a new scholar account and
 * its temporary password, to be changed at first sign-in. Without: an existing account that now has
 * the scholarship ($converted: it was a student account and is now a scholar account). Always points
 * to the Scholar login. Sent immediately (not queued), so the password is never stored in a job.
 */
class ScholarAccountCreated extends Mailable
{
    /**
     * @param  Scholar  $scholar  the new scholar record
     * @param  string|null  $password  the temporary password of a NEW account; null for an existing account
     * @param  bool  $converted  true when a student account was turned into a scholar account
     */
    public function __construct(public Scholar $scholar, public ?string $password, public bool $converted = false) {}

    /** Subject: the login details for a new account, or a "you were added" notice for an existing one. */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->password
                ? 'Your BAKR scholar account: '.$this->scholar->scholarship->shortName()
                : 'You were added as a scholar: '.$this->scholar->scholarship->shortName(),
        );
    }

    /** The e-mail body (resources/views/emails/scholar-account.blade.php) with the Scholar login link. */
    public function content(): Content
    {
        return new Content(view: 'emails.scholar-account', with: [
            'loginUrl' => route('scholar.login'),
            'forgotUrl' => route('student.password.request', ['from' => 'scholar']),
        ]);
    }
}
