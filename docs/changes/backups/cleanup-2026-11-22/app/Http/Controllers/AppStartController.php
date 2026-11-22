<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Where the installed BAKR app opens (manifest start_url = /app).
 *   signed in → their own portal's home (each portal still checks role, status and the
 *               temporary-password rule itself)
 *   guest     → the app start page: pick Student, Scholar or Provider and sign in
 */
class AppStartController extends Controller
{
    public function __invoke(Request $request): RedirectResponse|View
    {
        $route = match ($request->user()?->role) {
            'student' => 'student.dashboard',
            'scholar' => 'scholar.overview',
            'provider' => 'provider.dashboard',
            'admin' => 'admin.overview',
            default => null,
        };

        return $route ? redirect()->route($route) : view('auth.app-start');
    }
}
