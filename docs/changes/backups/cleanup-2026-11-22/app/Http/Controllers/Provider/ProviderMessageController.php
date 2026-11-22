<?php

namespace App\Http\Controllers\Provider;

use App\Mail\ScholarMessageReply;
use App\Services\Provider\ScholarAccounts;
use App\Services\ScholarNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

/**
 * Provider › Messages (owner, 2026-11-20): correction requests from the provider's OWN scholars
 * ("Request a correction" on the scholar's Profile). They never go to the admin. Every message id
 * from the URL is looked up inside ProviderScope::messages(), so another provider's message is a 404.
 * The reply is saved on the message (the scholar sees it on their Profile) and e-mailed to the scholar.
 */
class ProviderMessageController extends ProviderController
{
    public function index(Request $request): View
    {
        $scope = $this->scope($request);
        $show = $request->query('show') === 'all' ? 'all' : 'open';

        return $this->page($scope, 'provider.messages', [
            'show' => $show,
            'messages' => $scope->messages()->with(['scholar.user', 'scholar.scholarship'])
                ->when($show === 'open', fn ($q) => $q->whereNull('handled_at'))
                ->latest()->latest('id')->paginate(20)->withQueryString(),
            'openCount' => $scope->messages()->whereNull('handled_at')->count(),
        ]);
    }

    /** Reply to the scholar: kept on the message (shown on their Profile), e-mailed, and the message is marked handled. */
    public function reply(Request $request, int $message): RedirectResponse
    {
        $scope = $this->scope($request);
        $m = $scope->message($message)->load(['scholar.user', 'scholar.scholarship']);
        $data = $request->validate(['reply' => ['required', 'string', 'min:10', 'max:4000']], [
            'reply.required' => 'Write your reply first.',
            'reply.min' => 'Write at least a short sentence so the reply makes sense.',
        ]);

        $m->forceFill([
            'reply' => trim($data['reply']),
            'replied_by' => $request->user()->id,
            'replied_at' => now(),
            'reply_emailed_at' => null,
            'handled_at' => $m->handled_at ?? now(),
        ])->save();
        // In-portal note on the scholar's Overview (2026-11-21); the reply itself is e-mailed below.
        ScholarNotifier::send($m->scholar, 'reply', $scope->name().' answered your request',
            'Your request about your '.mb_strtolower($m->topicShort()).' has an answer. Read it on your Profile.',
            route('scholar.profile').'#requests', mail: false);

        $to = $m->scholar->contactEmail();
        if (! $to) {
            return back()->with('notice', 'Your reply is saved and shows on the scholar\'s Profile, but they have no e-mail address to send it to.');
        }

        try {
            Mail::to($to)->send(new ScholarMessageReply($m, $scope->name(), $request->user()->email));
        } catch (Throwable $e) {
            report($e);

            return back()->with('notice', 'Your reply is saved and shows on the scholar\'s Profile, but the e-mail to '.$to.' could not be sent.');
        }

        if (ScholarAccounts::mailIsTestMode()) {
            return back()->with('notice', 'E-mail is in test mode (MAIL_MAILER=log): the reply to '.$to.' was only written to the server log. It is saved, shows on the scholar\'s Profile, and the message is marked handled.');
        }

        $m->forceFill(['reply_emailed_at' => now()])->save();

        return $this->sent(back(), 'Reply sent', 'Your reply was e-mailed to '.$to.' and shows on their Profile. The message is marked handled.');
    }

    /** Mark handled / reopen. */
    public function update(Request $request, int $message): RedirectResponse
    {
        $m = $this->scope($request)->message($message);
        $action = $request->validate(['action' => ['required', Rule::in(['handled', 'reopen'])]])['action'];

        $m->forceFill(['handled_at' => $action === 'handled' ? now() : null])->save();

        return back()->with('status', $action === 'handled' ? 'Marked as handled.' : 'Moved back to open messages.');
    }
}
