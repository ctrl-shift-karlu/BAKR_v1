<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Services\Provider\ProviderScope;
use App\Support\AcademicTerm;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Base for every Provider Portal page. The provider always comes from the signed-in user
 * (ProviderScope::for), and every record is looked up inside that scope, never by raw ID.
 */
abstract class ProviderController extends Controller
{
    /** Everything the signed-in provider may see (403 when the account is not a known provider). */
    protected function scope(Request $request): ProviderScope
    {
        return ProviderScope::for($request->user());
    }

    /** The term picked in the query string, if it is one of the offered terms; else the current term. */
    protected function term(Request $request): string
    {
        $term = $request->query('term');

        return AcademicTerm::isValid($term) ? $term : AcademicTerm::current();
    }

    /** Renders a provider page with the sidebar counters every page shows. */
    protected function page(ProviderScope $scope, string $view, array $data = []): View
    {
        return view($view, $data + [
            'scope' => $scope,
            'provider' => $scope->provider,
            'navCounts' => [
                // Scholars needing attention: Inactive (overdue maintaining documents). Not the total.
                'scholars' => $scope->scholars()->whereStanding('inactive')->count(),
                'disbursements' => $scope->has('disbursements') ? $scope->disbursements()->where('term', AcademicTerm::current())->where('status', 'pending')->count() : 0,
                'requirements' => $scope->has('requirements') ? $scope->requirements()->where('status', 'submitted')->count() : 0,
                // Correction requests from the provider's scholars that are not handled yet.
                'messages' => $scope->messages()->whereNull('handled_at')->count(),
            ],
        ]);
    }
}
