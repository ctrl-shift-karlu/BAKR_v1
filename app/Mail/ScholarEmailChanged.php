<?php

namespace App\Mail;

use App\Models\Scholar;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Sent to the OLD address after the scholar confirmed a new sign-in e-mail (ScholarEmailChange), so a change they
 * did not expect does not go unnoticed. The old address may no longer work; a failure is only reported.
 */
class ScholarEmailChanged extends Mailable
{
    /**
     * @param  Scholar  $scholar  the scholar (already on the new e-mail)
     * @param  string  $newEmail  the address they sign in with from now on
     * @param  bool  $newLogin  true when new login details (a temporary password) were sent to the new address
     */
    public function __construct(public Scholar $scholar, public string $newEmail, public bool $newLogin = false) {}

    /** Subject line. */
    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Your BAKR sign-in e-mail was changed');
    }

    /** The body (resources/views/emails/scholar-email-changed.blade.php). */
    public function content(): Content
    {
        return new Content(view: 'emails.scholar-email-changed', with: ['loginUrl' => route('scholar.login')]);
    }
}
