<?php

use App\Http\Middleware\EnsureDatabaseIsReachable;
use App\Http\Middleware\EnsurePasswordChanged;
use App\Http\Middleware\ProviderFeature;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\SecurityHeaders;
use App\Support\DatabaseStatus;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'provider.feature' => ProviderFeature::class,
            'password.changed' => EnsurePasswordChanged::class,
        ]);

        // X-Frame-Options, nosniff, Referrer-Policy, Permissions-Policy on every page (security audit 2026-11-22).
        $middleware->web(append: [SecurityHeaders::class]);

        // Database off (XAMPP's MySQL stopped): open the PDO connection first, inside try / catch, and show a friendly
        // 503 page instead of the technical error (2026-11-23). Before the session, which is stored in the database.
        $middleware->web(prepend: [EnsureDatabaseIsReachable::class]);

        // HTTPS tunnels (cloudflared / ngrok) and reverse proxies: trust their X-Forwarded-* headers so
        // asset() / route() build https:// links (the installable app needs HTTPS). Only this machine by
        // default; on a real host set TRUSTED_PROXIES to the proxy's IPs (comma-separated).
        $middleware->trustProxies(at: array_filter(array_map('trim', explode(',', (string) env('TRUSTED_PROXIES', '127.0.0.1,::1')))));

        $middleware->redirectGuestsTo(fn ($request) => match (true) {
            $request->is('student/*') => url('studentLogin'),
            $request->is('provider/*') => url('providerLogin'),
            $request->is('admin', 'admin/*') => url('adminLogin'),
            default => url('scholarLogin'),   // scholar/* and anything else
        });

        // Signed-in users who open a login/register page go to their own dashboard.
        $middleware->redirectUsersTo(fn ($request) => match ($request->user()?->role) {
            'student' => route('student.dashboard'),
            'scholar' => route('scholar.overview'),
            'provider' => route('provider.dashboard'),
            'admin' => route('admin.overview'),
            default => url('/'),
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // The database stopped answering later in the request (PDOException / QueryException from a lost or refused
        // connection): the same friendly 503 page, never the SQL, the stack trace or the host / user. Other
        // database errors keep the normal handling. Logged once by DatabaseStatus (code only), not twice.
        $exceptions->render(fn (Throwable $e, Request $request) => DatabaseStatus::isUnavailable($e)
            ? DatabaseStatus::response($request, $e)
            : null);
        $exceptions->dontReportWhen(fn (Throwable $e) => DatabaseStatus::isUnavailable($e));
    })->create();
