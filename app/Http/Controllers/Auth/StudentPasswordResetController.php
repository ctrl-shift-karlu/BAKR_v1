<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;

/**
 * "Forgot password" for every real BAKR account, built on Laravel's password broker
 * (password_reset_tokens table, 60-minute tokens, 60-second resend throttle):
 * students, scholars, providers and the admin (each its own role and login page; /forgotPassword
 * redirects here). After the reset each person is sent to their
 * own login page.
 *
 * Only ACTIVE accounts with a role in RESETTABLE_ROLES are sent a link. The request step always
 * answers with the same message, so the form cannot be used to find out which e-mails exist.
 */
class StudentPasswordResetController extends Controller
{
    public const RESETTABLE_ROLES = ['student', 'scholar', 'provider', 'admin'];

    /** Each role's login page: "Back to login" (?from=… on the request page) and after the reset. */
    private const LOGINS = ['student' => 'student.login', 'scholar' => 'scholar.login', 'provider' => 'provider.login', 'admin' => 'admin.login'];

    /** Same reply whether or not the email belongs to an account. */
    private const SENT_MESSAGE = 'If that e-mail belongs to a BAKR account, a reset link is on its way. Check your inbox (and spam folder). The link works for 60 minutes.';

    public function showRequest(Request $request): View
    {
        return view('auth.forgot-password', ['loginRoute' => self::LOGINS[(string) $request->query('from')] ?? 'student.login']);
    }

    public function sendLink(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $user = $this->account($request->string('email')->toString());
        if ($user) {
            // The lookup above already applied role + status; the broker finds the same row by e-mail.
            Password::sendResetLink(['email' => $user->email, 'role' => $user->role, 'status' => 'active']);
        }

        return back()->with('status', self::SENT_MESSAGE);
    }

    public function showReset(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => (string) $request->query('email', ''),
        ]);
    }

    public function reset(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)->letters()->numbers()],
        ]);

        $account = $this->account($data['email']);
        $status = $account ? Password::reset(
            [
                'email' => $account->email,
                'password' => $data['password'],
                'password_confirmation' => $request->input('password_confirmation'),
                'token' => $data['token'],
                'role' => $account->role,
                'status' => 'active',
            ],
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                    'must_change_password' => false,   // they chose their own password (replaces a temporary one too)
                ])->save();
                $user->endSessions();   // whoever was signed in as this account (anywhere) is signed out

                event(new PasswordReset($user));
            }
        ) : Password::INVALID_USER;

        if ($status !== Password::PASSWORD_RESET) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'This reset link doesn\'t work or has expired. Ask for a new one from the login page.']);
        }

        // The link also works while signed in (sent from the scholar Settings page): end that session.
        if (Auth::check()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return redirect()->route(self::LOGINS[$account->role] ?? 'student.login')
            ->with('status', 'Your password has been changed. Sign in with your new password.');
    }

    /** An active account that may reset its password here, by e-mail (any case). */
    private function account(string $email): ?User
    {
        return User::query()
            ->whereRaw('LOWER(email) = ?', [Str::lower(trim($email))])
            ->whereIn('role', self::RESETTABLE_ROLES)
            ->where('status', 'active')
            ->first();
    }
}
