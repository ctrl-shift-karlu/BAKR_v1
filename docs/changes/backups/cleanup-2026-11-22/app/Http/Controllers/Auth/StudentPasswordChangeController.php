<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;

/**
 * "Change your password" for a signed-in student OR scholar (routes student.password.change* and
 * scholar.password.change*; the role picks the portal). Required at first sign-in for scholar
 * accounts a provider created (temporary password e-mailed): EnsurePasswordChanged keeps them on
 * this page until it is done. Scholars can also change it any time from Settings.
 */
class StudentPasswordChangeController extends Controller
{
    public function show(Request $request): View
    {
        $portal = $this->portal($request);

        return view('auth.change-password', [
            'forced' => (bool) $request->user()->must_change_password,
            'updateRoute' => $portal.'.password.change.update',
            'logoutRoute' => $portal.'.logout',
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', 'different:current_password', PasswordRule::min(8)->letters()->numbers()],
        ], [
            'current_password.current_password' => $request->user()->must_change_password
                ? 'This is not your current password. Use the temporary password from the e-mail.'
                : 'This is not your current password.',
            'password.different' => 'Choose a new password, not the current one.',
        ]);

        $user = $request->user();
        $wasForced = (bool) $user->must_change_password;
        $user->forceFill([
            'password' => Hash::make($data['password']),
            'must_change_password' => false,
            'remember_token' => Str::random(60),
        ])->save();
        $request->session()->regenerate();
        $user->endSessions(keep: $request->session()->getId());   // other browsers / devices are signed out

        $message = 'Your new password is saved. Use it the next time you sign in.';
        if (! $wasForced) {
            return back()->with('status', $message);   // changed from Settings: stay there
        }

        // First sign-in done: into the account's own portal.
        return redirect()->route($this->portal($request) === 'scholar' ? 'scholar.overview' : 'student.dashboard')->with('status', $message);
    }

    /** 'scholar' for scholar accounts, else 'student' (the route group already checked the role). */
    private function portal(Request $request): string
    {
        return $request->user()->role === 'scholar' ? 'scholar' : 'student';
    }
}
