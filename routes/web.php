<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\AppStartController;
use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\Auth\ProviderAuthController;
use App\Http\Controllers\Auth\ScholarAuthController;
use App\Http\Controllers\Auth\StudentAuthController;
use App\Http\Controllers\Auth\StudentPasswordChangeController;
use App\Http\Controllers\Auth\StudentPasswordResetController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\FindMatchesController;
use App\Http\Controllers\Provider\ProviderDashboardController;
use App\Http\Controllers\Provider\ProviderDisbursementController;
use App\Http\Controllers\Provider\ProviderHelpController;
use App\Http\Controllers\Provider\ProviderMessageController;
use App\Http\Controllers\Provider\ProviderRequirementController;
use App\Http\Controllers\Provider\ProviderScholarController;
use App\Http\Controllers\Provider\ProviderScholarRosterController;
use App\Http\Controllers\Provider\ProviderScholarshipController;
use App\Http\Controllers\Scholar\ScholarPortalController;
use App\Http\Controllers\Scholar\ScholarRequirementController;
use App\Http\Controllers\ScholarshipCatalogController;
use App\Http\Controllers\Student\StudentApplicationController;
use App\Http\Controllers\Student\StudentDashboardController;
use App\Http\Controllers\Student\StudentPortalController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| Paths here match the asset('...') links used in the Blade views, so every
| link resolves to exactly one URL.
*/

// ---- Public pages ----
Route::view('/', 'home')->name('home');
Route::redirect('/home', '/');
Route::get('about', [AboutController::class, 'show'])->name('about');
Route::get('contact', [ContactController::class, 'show'])->name('contact');
Route::view('privacy', 'privacy')->name('privacy');   // Privacy Policy (Data Privacy Act of 2012), linked from every portal

// "Find a Scholarship": sends each visitor to the right step (register / questionnaire / matches / provider dashboard).
Route::get('find-my-matches', FindMatchesController::class)->name('find');

// Installed app (PWA, public/manifest.json start_url): signed in → own portal, guest → pick a portal.
Route::get('app', AppStartController::class)->name('app.start');
Route::post('contact', [ContactController::class, 'store'])->middleware('throttle:5,10')->name('contact.store');
Route::post('about/feedback', [AboutController::class, 'feedback'])->middleware('throttle:5,10')->name('about.feedback');   // star-rating feedback (contact_messages, topic feedback)
Route::get('scholarships', [ScholarshipCatalogController::class, 'index'])->name('scholarships');
// The public Scholarship Finder was removed on 2026-11-19 (owner): guests only browse the scholarships;
// matching needs a student account (matching form + Gemini AI matcher). Old links go to "Find a Scholarship".
Route::redirect('scholarship-finder', '/find-my-matches');
// Old staff mock-up address: now the real reset flow (works for student, scholar and provider accounts).
Route::get('forgotPassword', fn (Request $r) => redirect()->route('student.password.request', array_filter(['from' => $r->query('from')])));

// ---- Sign-in: students (matching) and scholars (monitoring) are SEPARATE accounts ----
Route::middleware('guest')->group(function () {
    Route::get('studentLogin', [StudentAuthController::class, 'showLogin'])->name('student.login');
    Route::post('studentLogin', [StudentAuthController::class, 'login'])->middleware('throttle:6,1');
    Route::get('studentRegister', [StudentAuthController::class, 'showRegister'])->name('student.register');
    Route::post('studentRegister', [StudentAuthController::class, 'register'])->middleware('throttle:10,1');

    // Scholar accounts are created by providers (no self-registration).
    Route::get('scholarLogin', [ScholarAuthController::class, 'showLogin'])->name('scholar.login');
    Route::post('scholarLogin', [ScholarAuthController::class, 'login'])->middleware('throttle:6,1');

    // Forgot password: students, scholars and providers (each sent back to their own login).
    Route::get('student/forgot-password', [StudentPasswordResetController::class, 'showRequest'])->name('student.password.request');
    Route::post('student/forgot-password', [StudentPasswordResetController::class, 'sendLink'])
        ->middleware('throttle:5,10')->name('student.password.email');
});

// The e-mailed reset link must also work for someone still signed in (a scholar can send it from
// Settings), so these two are not guest-only. 'password.reset' is the name Laravel's reset e-mail links to.
Route::get('student/reset-password/{token}', [StudentPasswordResetController::class, 'showReset'])->name('password.reset');
Route::post('student/reset-password', [StudentPasswordResetController::class, 'reset'])
    ->middleware('throttle:6,1')->name('student.password.update');

Route::post('student/logout', [StudentAuthController::class, 'logout'])
    ->middleware('auth')
    ->name('student.logout');

// ---- Student area (role student): dashboard, matching form, matches ----
Route::middleware(['auth', 'role:student', 'password.changed'])
    ->prefix('student')
    ->name('student.')
    ->group(function () {
        Route::get('password', [StudentPasswordChangeController::class, 'show'])->name('password.change');
        Route::put('password', [StudentPasswordChangeController::class, 'update'])->middleware('throttle:6,1')->name('password.change.update');

        Route::get('dashboard', [StudentDashboardController::class, 'index'])->name('dashboard');
        Route::get('results', [StudentDashboardController::class, 'results'])->name('results');
        Route::get('profile', [StudentDashboardController::class, 'profile'])->name('profile');
        Route::get('profile/versions/{version}', [StudentDashboardController::class, 'version'])->whereNumber('version')->name('profile.version');

        Route::get('application', [StudentApplicationController::class, 'show'])->name('application.show');
        Route::post('application', [StudentApplicationController::class, 'store'])->name('application.store');
        Route::get('application/print', [StudentApplicationController::class, 'printable'])->name('application.print');

        // Gemini AI matcher: runs once per saved application (the results page asks in the background).
        Route::post('results/ai-match', [StudentDashboardController::class, 'aiMatch'])
            ->middleware('throttle:6,1')->name('results.ai-match');
        // AI explanations: one request per click, limited per student.
        Route::post('results/explain', [StudentDashboardController::class, 'explainAll'])
            ->middleware('throttle:6,1')->name('results.explain');
        Route::post('matches/{match}/explain', [StudentDashboardController::class, 'explainMatch'])
            ->middleware('throttle:12,1')->whereNumber('match')->name('matches.explain');
        // No "share with provider" (2026-11-18): students apply with the provider directly,
        // and the provider adds accepted scholars to BAKR, which creates their SCHOLAR account.

        Route::get('scholarships', [StudentPortalController::class, 'scholarships'])->name('scholarships');
        Route::get('help', [StudentPortalController::class, 'help'])->name('help');
        Route::post('help', [StudentPortalController::class, 'message'])->middleware('throttle:5,10')->name('help.message');
    });

// ---- Scholar portal (role scholar, signed in at /scholarLogin): monitoring only, no matching ----
Route::post('scholar/logout', [ScholarAuthController::class, 'logout'])->middleware('auth')->name('scholar.logout');

Route::middleware(['auth', 'role:scholar', 'password.changed'])
    ->prefix('scholar')
    ->name('scholar.')
    ->group(function () {
        // First sign-in with the e-mailed temporary password (EnsurePasswordChanged keeps them here).
        Route::get('password', [StudentPasswordChangeController::class, 'show'])->name('password.change');
        Route::put('password', [StudentPasswordChangeController::class, 'update'])->middleware('throttle:6,1')->name('password.change.update');

        Route::get('/', [ScholarPortalController::class, 'overview'])->name('overview');
        Route::get('scholarship', [ScholarPortalController::class, 'scholarship'])->name('scholarship');
        Route::get('requirements', [ScholarPortalController::class, 'requirements'])->name('requirements');
        Route::post('requirements/{requirement}/upload', [ScholarRequirementController::class, 'upload'])
            ->middleware('throttle:20,1')->whereNumber('requirement')->name('requirements.upload');
        Route::get('requirements/{requirement}/file', [ScholarRequirementController::class, 'download'])
            ->whereNumber('requirement')->name('requirements.file');

        // Account (modelled on the scholar prototype): Profile (details) and Settings (password, sign out).
        Route::get('profile', [ScholarPortalController::class, 'profile'])->name('profile');
        // "Request a correction": goes to the provider of the chosen scholarship (provider › Messages), not the admin.
        Route::post('profile/corrections', [ScholarPortalController::class, 'requestCorrection'])->middleware('throttle:5,10')->name('profile.correction');
        Route::get('settings', [ScholarPortalController::class, 'settings'])->name('settings');
        Route::post('settings/reset-link', [ScholarPortalController::class, 'sendResetLink'])->middleware('throttle:3,10')->name('settings.reset-link');

        // Explore
        Route::get('scholarships', [ScholarPortalController::class, 'scholarships'])->name('scholarships');
        Route::get('help', [ScholarPortalController::class, 'help'])->name('help');
        Route::post('help', [ScholarPortalController::class, 'message'])->middleware('throttle:5,10')->name('help.message');
    });

// ---- Provider Portal: real auth. Data is picked from the signed-in user's provider_key, never the URL ----
Route::middleware('guest')->group(function () {
    Route::get('providerLogin', [ProviderAuthController::class, 'showLogin'])->name('provider.login');
    Route::post('providerLogin', [ProviderAuthController::class, 'login'])->middleware('throttle:6,1');
});

Route::middleware(['auth', 'role:provider'])
    ->prefix('provider')
    ->name('provider.')
    ->group(function () {
        Route::get('dashboard', [ProviderDashboardController::class, 'index'])->name('dashboard');
        // No Applicants page (2026-11-18): providers monitor the scholars they already have.

        Route::get('scholars', [ProviderScholarController::class, 'index'])->name('scholars');
        // Official roster: add one scholar, or import many from an Excel (.xlsx) / CSV file.
        Route::get('scholars/new', [ProviderScholarRosterController::class, 'create'])->name('scholars.create');
        Route::post('scholars', [ProviderScholarRosterController::class, 'store'])->middleware('throttle:30,1')->name('scholars.store');
        Route::post('scholars/import', [ProviderScholarRosterController::class, 'import'])->middleware('throttle:10,1')->name('scholars.import');
        Route::get('scholars/template', [ProviderScholarRosterController::class, 'template'])->name('scholars.template');
        Route::post('scholars/{scholar}/login', [ProviderScholarRosterController::class, 'resendLogin'])
            ->middleware('throttle:6,1')->whereNumber('scholar')->name('scholars.login');
        Route::get('scholars/{scholar}', [ProviderScholarController::class, 'show'])->whereNumber('scholar')->name('scholars.show');
        Route::patch('scholars/{scholar}', [ProviderScholarController::class, 'update'])->whereNumber('scholar')->name('scholars.update');
        // Terminate / reinstate: only the scholarship's own provider (ProviderScope), behind a confirmation.
        Route::patch('scholars/{scholar}/status', [ProviderScholarController::class, 'status'])->whereNumber('scholar')->name('scholars.status');

        // Optional pages (config/providers.php 'features'): 404 for providers that don't use them.
        Route::middleware('provider.feature:disbursements')->group(function () {
            Route::get('disbursements', [ProviderDisbursementController::class, 'index'])->name('disbursements');
            Route::post('disbursements/generate', [ProviderDisbursementController::class, 'generate'])->name('disbursements.generate');
            Route::post('disbursements/release', [ProviderDisbursementController::class, 'release'])->name('disbursements.release');
            Route::post('disbursements/{disbursement}/release', [ProviderDisbursementController::class, 'releaseOne'])
                ->whereNumber('disbursement')->name('disbursements.release-one');
        });

        Route::middleware('provider.feature:requirements')->group(function () {
            Route::get('requirements', [ProviderRequirementController::class, 'index'])->name('requirements');
            // Each scholarship's deadline per semester: saving it opens the checklist and e-mails the scholars.
            Route::post('requirements/deadline', [ProviderRequirementController::class, 'deadline'])
                ->middleware('throttle:30,1')->name('requirements.deadline');
            Route::patch('requirements/{requirement}', [ProviderRequirementController::class, 'review'])
                ->whereNumber('requirement')->name('requirements.review');
            Route::get('requirements/{requirement}/file', [ProviderRequirementController::class, 'download'])
                ->whereNumber('requirement')->name('requirements.file');
        });

        Route::get('scholarships', [ProviderScholarshipController::class, 'index'])->name('scholarships');
        Route::put('scholarships/{scholarship}', [ProviderScholarshipController::class, 'update'])
            ->middleware('throttle:20,1')->whereNumber('scholarship')->name('scholarships.update');
        // Maintaining documents (own form): only providers that collect requirements in BAKR (404 otherwise).
        Route::put('scholarships/{scholarship}/documents', [ProviderScholarshipController::class, 'updateDocuments'])
            ->middleware(['provider.feature:requirements', 'throttle:20,1'])->whereNumber('scholarship')->name('scholarships.documents');

        // Messages: correction requests from the provider's own scholars (ProviderScope::messages()).
        Route::get('messages', [ProviderMessageController::class, 'index'])->name('messages');
        Route::patch('messages/{message}', [ProviderMessageController::class, 'update'])->whereNumber('message')->name('messages.update');
        Route::post('messages/{message}/reply', [ProviderMessageController::class, 'reply'])
            ->middleware('throttle:20,1')->whereNumber('message')->name('messages.reply');

        Route::get('help', [ProviderHelpController::class, 'show'])->name('help');
        Route::post('help', [ProviderHelpController::class, 'message'])->middleware('throttle:5,10')->name('help.message');

        Route::post('logout', [ProviderAuthController::class, 'logout'])->name('logout');
    });

// ---- Admin portal: real auth (account from AdminAccountSeeder / .env). No Officer role (dropped 2026-11-17). ----
Route::middleware('guest')->group(function () {
    Route::get('adminLogin', [AdminAuthController::class, 'showLogin'])->name('admin.login');
    Route::post('adminLogin', [AdminAuthController::class, 'login'])->middleware('throttle:6,1');
});

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', [AdminController::class, 'overview'])->name('overview');
        Route::get('analytics', [AdminController::class, 'analytics'])->name('analytics');
        Route::get('analytics/export.xlsx', [AdminController::class, 'exportAnalytics'])->middleware('throttle:20,1')->name('analytics.export');
        Route::get('accounts', [AdminController::class, 'accounts'])->name('accounts');
        Route::patch('accounts/{user}/status', [AdminController::class, 'updateStatus'])->whereNumber('user')->name('accounts.status');
        Route::post('accounts/{user}/reset-link', [AdminController::class, 'sendReset'])->middleware('throttle:10,1')->whereNumber('user')->name('accounts.reset');
        Route::get('scholarships', [AdminController::class, 'scholarships'])->name('scholarships');
        Route::get('messages', [AdminController::class, 'messages'])->name('messages');
        Route::patch('messages/{message}', [AdminController::class, 'updateMessage'])->whereNumber('message')->name('messages.update');
        Route::post('messages/{message}/reply', [AdminController::class, 'replyMessage'])->whereNumber('message')->middleware('throttle:20,1')->name('messages.reply');
        Route::post('logout', [AdminAuthController::class, 'logout'])->name('logout');
    });

// Old static prototype addresses (design guides) now open the real portals.
Route::redirect('adminDashboardF', '/admin');
Route::redirect('scholarDashboardF', '/scholar');
