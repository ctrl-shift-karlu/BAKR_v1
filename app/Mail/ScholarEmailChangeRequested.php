<?php

namespace App\Mail;

use App\Models\Scholar;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Sent to the NEW address when a provider changes a scholar's sign-in e-mail (ScholarEmailChange). Opening the
 * link proves the address works; only then the e-mail changes. Sent immediately (not queued), so the link is
 * never stored in a job.
 */
class ScholarEmailChangeRequested extends Mailable
{
    /**
     * @param  Scholar  $scholar  the scholar whose e-mail is changing (pending_email = this address)
     * @param  string  $confirmUrl  the one-time confirmation link
     */
    public function __construct(public Scholar $scholar, public string $confirmUrl) {}

    /** Subject line. */
    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Confirm your new BAKR sign-in e-mail');
    }

    /** The body (resources/views/emails/scholar-email-confirm.blade.php). */
    public function content(): Content
    {
        return new Content(view: 'emails.scholar-email-confirm', with: ['hours' => Scholar::EMAIL_CHANGE_HOURS]);
    }
}
