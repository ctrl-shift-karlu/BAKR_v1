<?php

namespace App\Http\Controllers;

use App\Models\ScholarshipApplication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * One entry point for "Find a Scholarship" / "Find My Matches". Sends each visitor to the
 * step that makes sense for them instead of a page that will refuse or repeat work:
 *   guest                     → register (the login page links back for returning students)
 *   student, no answers yet   → the matching form
 *   student with answers      → their matches
 *   scholar                   → their scholar portal (no matching)
 *   provider                  → their provider dashboard
 *   admin                     → the admin overview
 */
class FindMatchesController extends Controller
{
    /** Redirects the visitor to the right "Find a Scholarship" step for their account (see the class description). */
    public function __invoke(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('student.register');
        }

        return match ($user->role) {
            'student' => ScholarshipApplication::currentFor($user->id)->exists()
                ? redirect()->route('student.results')
                : redirect()->route('student.application.show'),
            // Scholar accounts have no matching: their own portal.
            'scholar' => redirect()->route('scholar.overview'),
            'provider' => redirect()->route('provider.dashboard'),
            // The admin has no matching either (same target as bootstrap/app.php redirectUsersTo).
            'admin' => redirect()->route('admin.overview'),
            default => redirect('/'),
        };
    }
}
