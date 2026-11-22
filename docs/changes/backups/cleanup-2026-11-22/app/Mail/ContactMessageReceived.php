<?php

namespace App\Mail;

use App\Models\ContactMessage;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class ContactMessageReceived extends Mailable
{
    public function __construct(public ContactMessage $contact) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[BAKR] '.$this->contact->topicLabel().' from '.$this->contact->name,
            replyTo: [new Address($this->contact->email, $this->contact->name)],   // "Reply" goes straight to the student
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.contact-message');
    }
}
