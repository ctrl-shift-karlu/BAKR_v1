<?php

namespace App\Http\Controllers\Provider;

use App\Models\ContactMessage;
use App\Services\ContactMessageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Provider Help: about BAKR for providers, what they can and can't see, and a message form. */
class ProviderHelpController extends ProviderController
{
    public function show(Request $request): View
    {
        $scope = $this->scope($request);

        return $this->page($scope, 'provider.help', ['topics' => self::topics()]);
    }

    /**
     * What a provider can write about. "Scholarship info needs updating" is left out (owner, 2026-11-19):
     * providers edit their own scholarships on My scholarships, so there is nothing to ask the BAKR team to change.
     */
    public static function topics(): array
    {
        return array_diff_key(ContactMessage::TOPICS, ['info' => true]);
    }

    public function message(Request $request, ContactMessageService $messages): RedirectResponse
    {
        $scope = $this->scope($request);
        $data = $this->validateHelpMessage($request, self::topics());

        // The provider is named in the message so the BAKR team knows who wrote it.
        $messages->record([
            'topic' => $data['topic'],
            'name' => $scope->name().' (provider)',
            'email' => $request->user()->email,
            'message' => $data['message'],
        ]);

        return $this->sent(redirect()->route('provider.help'), 'Message sent', 'The BAKR team will reply to '.$request->user()->email.'.');
    }
}
