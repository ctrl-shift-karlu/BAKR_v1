<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactMessageRequest;
use App\Models\ContactMessage;
use App\Services\ContactMessageService;

class ContactController extends Controller
{
    public function show()
    {
        $feedback = ContactMessage::publicFeedback()->latest('approved_at')->limit(6)->get();
        $average = ContactMessage::publicFeedback()->whereNotNull('rating')->avg('rating');

        return view('contact', compact('feedback', 'average'));
    }

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
