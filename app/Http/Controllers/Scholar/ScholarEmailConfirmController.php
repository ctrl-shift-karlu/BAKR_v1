<?php

namespace App\Http\Controllers\Scholar;

use App\Http\Controllers\Controller;
use App\Models\Scholar;
use App\Services\Provider\ScholarEmailChange;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * The scholar opened the "confirm your new e-mail" link (owner, 2026-11-23). Only now the sign-in e-mail changes
 * (ScholarEmailChange::confirm). Works whether or not someone is signed in; shows the outcome in words.
 */
class ScholarEmailConfirmController extends Controller
{
    /** Confirms the change and shows what happened (changed / expired / invalid / taken). */
    public function __invoke(Request $request, int $scholar, string $token, ScholarEmailChange $change): View
    {
        [$state, $s, $loginSent] = $change->confirm($scholar, $token);

        return view('auth.email-confirmed', [
            'state' => $state,
            'email' => $state === 'changed' ? $s?->contactEmail() : null,
            'provider' => $s?->scholarship?->provider,
            'loginSent' => $loginSent,
            'hours' => Scholar::EMAIL_CHANGE_HOURS,
        ]);
    }
}
