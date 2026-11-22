<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\StudentRegisterRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

/**
 * Student (matching) accounts: self-registration, sign-in and sign-out at /studentRegister and /studentLogin.
 * Role and status are always set here on the server, never taken from the form.
 */
class StudentAuthController extends Controller
{
    /** Shows the student registration page. */
    public function showRegister(): View
    {
        return view('auth.register');
    }

    /**
     * Creates a student account from the validated form, signs the student in and opens the matching form.
     * The password is hashed by the User model's "hashed" cast.
     */
    public function register(StudentRegisterRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $user = new User;
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->password = Hash::make($data['password']);
        // Role and status are set server-side only, never from the request.
        $user->role = 'student';
        $user->status = 'active';
        $user->save();

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('student.application.show');
    }

    /** Shows the student sign-in page. */
    public function showLogin(): View
    {
        return view('auth.student-login');
    }

    /**
     * Signs in an ACTIVE student account ("Keep me signed in" = remember cookie). Scholar and provider
     * accounts are refused here; they have their own sign-in pages.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Only active STUDENT accounts may sign in here. Scholar accounts (created by providers) use
        // the Scholar login; providers use the Provider login.
        $ok = Auth::attempt($credentials + ['role' => 'student', 'status' => 'active'], $request->boolean('remember'));

        if (! $ok) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'These credentials do not match a student account. Scholars sign in at the Scholar login.']);
        }

        $request->session()->regenerate();

        if (Auth::user()->must_change_password) {
            return redirect()->route('student.password.change');
        }

        return $this->intendedWithin('student', route('student.dashboard'));
    }

    /** Signs the student out, destroys the session and issues a new CSRF token. */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
