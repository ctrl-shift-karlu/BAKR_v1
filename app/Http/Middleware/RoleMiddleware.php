<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Usage: Route::middleware(['auth', 'role:student'])  (or role:provider,admin)
 * Role-level gate only; record ownership is enforced by query scoping.
 * An account the admin deactivated is signed out here at its next request (the logins
 * already refuse inactive accounts).
 */
class RoleMiddleware
{
    /**
     * Signs out a deactivated account, then allows the request only for the listed roles (403 otherwise).
     *
     * @param  string  ...$roles  the allowed roles from the route, e.g. role:student
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if ($user && $user->status !== 'active') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            abort_if($request->expectsJson(), 403, 'This account has been deactivated.');

            return redirect('/')->with('notice', 'Your account has been deactivated. Please contact the BAKR team.');
        }

        abort_unless($user && in_array($user->role, $roles, true), 403);

        return $next($request);
    }
}
