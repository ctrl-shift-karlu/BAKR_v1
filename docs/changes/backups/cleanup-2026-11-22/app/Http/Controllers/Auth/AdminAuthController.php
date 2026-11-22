<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/** Admin sign-in (/adminLogin). The account comes from AdminAccountSeeder only (no registration). */
class AdminAuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.admin-login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Only active admin accounts may sign in here.
        if (! Auth::attempt($credentials + ['role' => 'admin', 'status' => 'active'])) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'These credentials do not match an administrator account.']);
        }

        $request->session()->regenerate();

        return $this->intendedWithin('admin', route('admin.overview'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
