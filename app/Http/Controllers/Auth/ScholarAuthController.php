<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Scholar login (/scholarLogin): real authentication for SCHOLAR accounts (users.role = scholar),
 * which providers create when they add a scholar. Separate from the student login: students use
 * matching (/studentLogin); scholars use the monitoring portal (/scholar). No self-registration.
 */
class ScholarAuthController extends Controller
{
    /** Shows the scholar sign-in page. */
    public function showLogin(): View
    {
        return view('auth.scholar-login');
    }

    /**
     * Signs in an ACTIVE scholar account. A scholar who still has the e-mailed temporary password is sent
     * to "Change your password" first; everyone else goes back to the scholar page they asked for, or Overview.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Only active scholar accounts may sign in here.
        $ok = Auth::attempt($credentials + ['role' => 'scholar', 'status' => 'active'], $request->boolean('remember'));

        if (! $ok) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'These credentials do not match a scholar account. Students sign in at the Student login.']);
        }

        $request->session()->regenerate();

        // Account just created by a provider: replace the e-mailed temporary password first.
        if (Auth::user()->must_change_password) {
            return redirect()->route('scholar.password.change');
        }

        return $this->intendedWithin('scholar', route('scholar.overview'));
    }

    /** Signs the scholar out, destroys the session and issues a new CSRF token. */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
