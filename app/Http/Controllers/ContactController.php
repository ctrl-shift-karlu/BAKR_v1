<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactMessageRequest;
use App\Models\ContactMessage;
use App\Services\ContactMessageService;

/** Public Contact page: shows approved public feedback and saves + e-mails messages to the BAKR team. */
class ContactController extends Controller
{
    /** Shows the Contact page with up to six approved feedback quotes and their average rating. */
    public function show()
    {
        $feedback = ContactMessage::publicFeedback()->latest('approved_at')->limit(6)->get();
        $average = ContactMessage::publicFeedback()->whereNotNull('rating')->avg('rating');

        return view('contact', compact('feedback', 'average'));
    }

    /**
     * Saves a contact message and e-mails it to the BAKR team (ContactMessageService).
     * A filled-in honeypot field ("website") means a bot: the page answers as if it worked but stores nothing.
     */
    public function store(ContactMessageRequest $request, ContactMessageService $messages)
    {
        $back = redirect()->to(url('contact').'#message');

        // Honeypot filled in: a bot. Look successful, store nothing.
        if ($request->filled('website')) {
            return $back->with('contact_sent', true);
        }

        $messages->record($request->validated(), $request->boolean('share_publicly'));

        return $back->with('contact_sent', true);
    }
}
