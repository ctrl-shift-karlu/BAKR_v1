<?php

namespace App\Mail;

use App\Models\ScholarMessage;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** A provider's answer to a scholar's correction request, sent from the provider's Messages page (not queued). */
class ScholarMessageReply extends Mailable
{
    /**
     * @param  ScholarMessage  $correction  the scholar's correction request
     * @param  string  $providerName  shown as the sender
     * @param  string|null  $providerEmail  where a reply from the scholar goes
     */
    public function __construct(public ScholarMessage $correction, public string $providerName, public ?string $providerEmail = null) {}

    /** Subject "Re: your correction request (<topic>) from <provider>"; a reply goes to the provider. */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Re: your correction request ('.$this->correction->topicShort().') from '.$this->providerName,
            replyTo: $this->providerEmail ? [new Address($this->providerEmail, $this->providerName)] : [],   // a reply goes to the provider
        );
    }

    /** The e-mail body (resources/views/emails/scholar-message-reply.blade.php). */
    public function content(): Content
    {
        return new Content(view: 'emails.scholar-message-reply');
    }
}
