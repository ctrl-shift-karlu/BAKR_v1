<?php

namespace App\Mail;

use App\Models\ContactMessage;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** E-mail to the BAKR team inbox (BAKR_CONTACT_TO) for every Contact, feedback or portal Help message. */
class ContactMessageReceived extends Mailable
{
    /**
     * @param  ContactMessage  $contact  the saved message
     */
    public function __construct(public ContactMessage $contact) {}

    /** Subject "[BAKR] <topic> from <name>"; pressing Reply answers the sender directly. */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[BAKR] '.$this->contact->topicLabel().' from '.$this->contact->name,
            replyTo: [new Address($this->contact->email, $this->contact->name)],   // "Reply" goes straight to the student
        );
    }

    /** The e-mail body (resources/views/emails/contact-message.blade.php). */
    public function content(): Content
    {
        return new Content(view: 'emails.contact-message');
    }
}
