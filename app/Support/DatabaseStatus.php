<?php

namespace App\Support;

use App\Http\Middleware\SecurityHeaders;
use Illuminate\Database\LostConnectionDetector;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PDOException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * "The database is off" (owner, 2026-11-23): XAMPP's MySQL stopped, wrong credentials, the `bakr` database missing.
 * Laravel talks to MySQL through PHP Data Objects (PDO); every connection failure surfaces as a PDOException
 * (or a QueryException wrapping one). This class recognises those failures and answers with a plain,
 * non-technical "Server error" page (HTTP 503, one "Try again" button) instead of the SQL error, the stack trace or
 * the database host and user. Visitors never see the word "database".
 *
 * Used twice: EnsureDatabaseIsReachable opens the PDO connection before anything else of the request (sessions and
 * cache live in the database too), and bootstrap/app.php renders the same page for a connection lost later on.
 * Other database errors (a bug in a query) are NOT hidden: they still reach the normal error handling.
 */
class DatabaseStatus
{
    /**
     * MySQL / MariaDB client and server codes that mean "cannot reach or open the database":
     * 1044 / 1045 access denied, 1049 unknown database, 2002 / 2003 cannot connect (server off),
     * 2005 unknown host, 2006 server gone away, 2013 lost connection during the query.
     */
    public const CONNECTION_CODES = [1044, 1045, 1049, 2002, 2003, 2005, 2006, 2013];

    /** Seconds a browser / the installed app is told to wait before trying again (Retry-After). */
    public const RETRY_AFTER = 30;

    /**
     * Opens the PDO connection now. Returns null when it works, or the PDOException that says why it does not
     * (Laravel connects lazily; this makes the failure happen in one known place, inside try / catch).
     */
    public static function check(?string $connection = null): ?PDOException
    {
        try {
            DB::connection($connection)->getPdo();

            return null;
        } catch (PDOException $e) {
            return $e;
        } catch (QueryException $e) {
            $previous = $e->getPrevious();

            return $previous instanceof PDOException ? $previous : new PDOException($e->getMessage(), 0, $e);
        }
    }

    /** True when $e (or anything it wraps) is a PDO "cannot reach / open the database" failure. */
    public static function isUnavailable(Throwable $e): bool
    {
        for ($current = $e; $current !== null; $current = $current->getPrevious()) {
            if (! $current instanceof PDOException && ! $current instanceof QueryException) {
                continue;
            }
            if ($current instanceof PDOException && in_array(self::driverCode($current), self::CONNECTION_CODES, true)) {
                return true;
            }
            if ((new LostConnectionDetector)->causedByLostConnection($current)) {
                return true;
            }
            // SQLite (tests, a local copy): the database file is missing or cannot be opened.
            if (str_contains($current->getMessage(), 'unable to open database file')) {
                return true;
            }
        }

        return false;
    }

    /**
     * The driver's own error number: PDOException::errorInfo[1] when PDO filled it in, else the number in
     * "SQLSTATE[HY000] [2002] …", else a numeric getCode(). Null when there is none.
     */
    public static function driverCode(PDOException $e): ?int
    {
        if (isset($e->errorInfo[1]) && is_numeric($e->errorInfo[1])) {
            return (int) $e->errorInfo[1];
        }
        if (preg_match('/SQLSTATE\[[0-9A-Z]+\]\s*\[(\d+)\]/', $e->getMessage(), $m)) {
            return (int) $m[1];
        }

        return is_numeric($e->getCode()) ? (int) $e->getCode() : null;
    }

    /**
     * The answer for a visitor while the database is unavailable: 503 + Retry-After. A page for browsers
     * (resources/views/errors/database.blade.php, which needs no database, session or login), a short JSON
     * message for fetch() calls. The technical reason goes only to the log (code, never the SQL).
     */
    public static function response(Request $request, ?Throwable $e = null): Response
    {
        if ($e) {
            Log::critical('BAKR database unavailable', [
                'code' => $e instanceof PDOException ? self::driverCode($e) : $e->getCode(),
                'path' => $request->path(),
            ]);
        }
        // This answer may skip the web middleware, so it carries the usual security headers itself.
        $headers = ['Retry-After' => (string) self::RETRY_AFTER, 'Cache-Control' => 'no-store'] + SecurityHeaders::HEADERS;
        // Visitors read plain words: a server problem, never "database" or the technical reason (owner, 2026-11-23).
        $message = 'BAKR is having a server problem at the moment. Please try again in a few minutes.';

        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 503, $headers);
        }

        try {
            return response()->view('errors.database', ['retry' => self::RETRY_AFTER], 503, $headers);
        } catch (Throwable) {
            // Even the page could not be drawn: plain text, still without technical details.
            return response($message, 503, $headers + ['Content-Type' => 'text/plain; charset=UTF-8']);
        }
    }
}
