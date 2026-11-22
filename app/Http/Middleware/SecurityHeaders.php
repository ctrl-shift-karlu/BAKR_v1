<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Basic browser security headers on every web response (security audit, 2026-11-22):
 *   X-Frame-Options: SAMEORIGIN   no other site can load BAKR in a frame (clickjacking on Terminate,
 *                                 Release, Deactivate…); BAKR's own pages still may (file previews).
 *   X-Content-Type-Options        browsers never guess a file's type (uploaded documents).
 *   Referrer-Policy               other sites see only "bakr", never a full URL such as a reset-link token.
 *   Permissions-Policy            camera, microphone and location are never used.
 * A header a controller already set (e.g. a file response) is kept.
 */
class SecurityHeaders
{
    private const HEADERS = [
        'X-Frame-Options' => 'SAMEORIGIN',
        'X-Content-Type-Options' => 'nosniff',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        'Permissions-Policy' => 'camera=(), microphone=(), geolocation=()',
    ];

    /** Adds the security headers above to the response, keeping any the controller already set. */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        foreach (self::HEADERS as $name => $value) {
            if (! $response->headers->has($name)) {
                $response->headers->set($name, $value);
            }
        }

        return $response;
    }
}
