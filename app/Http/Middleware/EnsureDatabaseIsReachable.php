<?php

namespace App\Http\Middleware;

use App\Support\DatabaseStatus;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * First thing on every web request (owner, 2026-11-23): open the PDO connection inside try / catch
 * (DatabaseStatus::check). When MySQL is off (XAMPP stopped) or refuses the login, the visitor gets the friendly
 * "BAKR is temporarily unavailable" page (503) instead of a technical error. Runs before the session starts,
 * because sessions are stored in the database too.
 */
class EnsureDatabaseIsReachable
{
    /** Continue when the database answers; otherwise answer with the unavailable page. */
    public function handle(Request $request, Closure $next): Response
    {
        $failure = DatabaseStatus::check();
        if ($failure !== null) {
            return DatabaseStatus::isUnavailable($failure)
                ? DatabaseStatus::response($request, $failure)
                : throw $failure;   // not a connection problem: normal error handling
        }

        return $next($request);
    }
}
