<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/** Provider Portal sign-in. Accounts are created by ProviderAccountSeeder only (no registration). */
class ProviderAuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.provider-login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Only active provider accounts may sign in here.
        $ok = Auth::attempt($credentials + ['role' => 'provider', 'status' => 'active']);

        // An account must also point at a provider that exists in config/providers.php.
        if ($ok && ! config()->has('providers.accounts.'.Auth::user()->provider_key)) {
            Auth::logout();
            $ok = false;
        }

        if (! $ok) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'These credentials do not match a provider account.']);
        }

        $request->session()->regenerate();

        return $this->intendedWithin('provider', route('provider.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('provider.login');
    }
}
