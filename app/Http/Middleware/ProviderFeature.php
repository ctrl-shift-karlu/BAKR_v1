<?php

namespace App\Http\Middleware;

use App\Services\Provider\ProviderScope;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Optional Provider Portal pages (config/providers.php 'features').
 * Usage: ->middleware('provider.feature:disbursements'). A provider without it gets 404,
 * the same answer as for a page that does not exist; hiding the menu item is not enough.
 * Runs after 'role:provider', so the user is always a provider here.
 */
class ProviderFeature
{
    /**
     * Answers 404 when the signed-in provider does not use the optional page.
     *
     * @param  string  $feature  'disbursements' or 'requirements' (from the route: provider.feature:<name>)
     */
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        abort_unless(ProviderScope::for($request->user())->has($feature), 404);

        return $next($request);
    }
}
