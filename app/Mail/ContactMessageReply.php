<?php

namespace App\Mail;

use App\Models\ContactMessage;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** The BAKR team's answer to a Contact / Help message, sent from the admin's Messages page (not queued). */
class ContactMessageReply extends Mailable
{
    public function __construct(public ContactMessage $contact, public string $body) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Re: '.$this->contact->topicLabel().' (BAKR)',
            replyTo: array_filter([config('bakr.contact_to') ? new Address(config('bakr.contact_to'), 'BAKR team') : null]),   // a reply goes back to the team inbox
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.contact-reply');
    }
}
