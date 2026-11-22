<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Scholar accounts created by a provider (ScholarAccounts) start with an e-mailed temporary password.
 * Until the person chooses their own (users.must_change_password = false), every page of their
 * portal sends them to "Change your password" (scholar.password.change, or student.password.change
 * for a student account). The change page itself and sign-out stay reachable.
 */
class EnsurePasswordChanged
{
    /** Lets the request through, or sends an account that still has a temporary password to the change page (403 for JSON requests). */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user?->must_change_password) {
            return $next($request);
        }

        $portal = $user->role === 'scholar' ? 'scholar' : 'student';
        if ($request->routeIs("{$portal}.password.change", "{$portal}.password.change.update")) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            abort(403, 'Change your temporary password first.');
        }

        return redirect()->route("{$portal}.password.change");
    }
}
