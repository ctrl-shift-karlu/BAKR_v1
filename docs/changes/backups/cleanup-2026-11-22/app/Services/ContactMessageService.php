<?php

namespace App\Services;

use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Saves a message to the BAKR team, then e-mails it. Used by the public Contact page and
 * by the Help pages inside the student and provider portals, so every message is handled
 * the same way: stored first (never lost if mail is down), then sent.
 */
class ContactMessageService
{
    /** @param array{topic: string, name: string, email: string, message: ?string, rating?: ?int, aspects?: ?array} $data */
    public function record(array $data, bool $sharePublicly = false): ContactMessage
    {
        $isFeedback = $data['topic'] === 'feedback';
        $aspects = $isFeedback ? array_values(array_intersect(array_keys(ContactMessage::ASPECTS), (array) ($data['aspects'] ?? []))) : [];

        $contact = ContactMessage::create([
            'topic' => $data['topic'],
            'name' => $data['name'],
            'email' => $data['email'],
            'message' => (string) ($data['message'] ?? ''),   // About feedback: the comment is optional
            'rating' => $isFeedback ? ($data['rating'] ?? null) : null,
            'aspects' => $aspects ?: null,
            'share_publicly' => $isFeedback && $sharePublicly,
        ]);

        try {
            Mail::to(config('bakr.contact_to'))->send(new ContactMessageReceived($contact));
            $contact->forceFill(['emailed_at' => now()])->save();
        } catch (Throwable $e) {
            report($e);   // logged; the row above still has the message (emailed_at stays empty)
        }

        return $contact;
    }
}
