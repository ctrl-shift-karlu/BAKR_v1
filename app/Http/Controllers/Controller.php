<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Base class of every BAKR controller, with the small helpers several portals share. */
abstract class Controller
{
    /**
     * The portal Help forms (student, scholar, provider): a topic from $topics and a short message.
     *
     * @param  array<string, string>  $topics  topic key => label
     * @return array{topic: string, message: string}
     */
    protected function validateHelpMessage(Request $request, array $topics): array
    {
        return $request->validate([
            'topic' => ['required', Rule::in(array_keys($topics))],
            'message' => ['required', 'string', 'min:10', 'max:2000'],
        ], [
            'message.min' => 'Please write at least a short sentence so we can help.',
        ]);
    }

    /**
     * "It was sent" confirmation (owner, 2026-11-20): instead of a green alert, the next page opens a
     * success modal (partials/portal/sent-dialog, in every portal layout). Use it only when something
     * was really SENT (a message, a reply, an e-mailed link or login); saves and warnings stay alerts.
     */
    protected function sent(RedirectResponse $redirect, string $title, string $text): RedirectResponse
    {
        return $redirect->with('sent', ['title' => $title, 'text' => $text]);
    }

    /**
     * After sign-in: the page the person was sent away from, but only when it belongs to the portal they just
     * signed in to (audit 2026-11-21). Opening /provider/… as a guest and then signing in at the Student login
     * used to send the student to that provider page (403); now they land on $default instead.
     */
    protected function intendedWithin(string $prefix, string $default): RedirectResponse
    {
        $intended = (string) session()->pull('url.intended', '');
        $base = rtrim(url($prefix), '/');
        $path = strtok($intended, '?#');

        return redirect()->to($path === $base || str_starts_with($path, $base.'/') ? $intended : $default);
    }
}
