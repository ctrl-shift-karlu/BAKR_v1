{{-- Shown while BAKR's server cannot work (App\Support\DatabaseStatus, 2026-11-23): e.g. XAMPP / MySQL stopped.
     Status 503. Needs NO database, session or login (no layout, no auth()). Plain words for visitors: it never says
     "database" or shows the technical reason, and offers only "Try again" (reloads this page). Styles:
     public/css/bakr-error.css. --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <meta name="theme-color" content="#143a2b">
    <title>Server error | {{ config('app.name', 'BAKR') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo/bakr-logo.png') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/bakr-error.css') }}">
    <script src="{{ asset('js/bakr-theme.js') }}"></script>
</head>
<body class="bk-error">
<main class="bk-error-card" aria-labelledby="bk-error-title">
    <img class="bk-error-logo" src="{{ asset('images/logo/bakr-logo.png') }}" alt="BAKR logo" width="64" height="64">
    <span class="bk-error-icon" aria-hidden="true"><i class="bi bi-exclamation-triangle"></i></span>
    <h1 id="bk-error-title">Server error</h1>
    <p>BAKR is having a server problem at the moment. Please try again in a few minutes.</p>
    <div class="bk-error-actions">
        <a class="bk-error-btn" href="{{ url()->current() }}"><i class="bi bi-arrow-clockwise" aria-hidden="true"></i> Try again</a>
    </div>
</main>
</body>
</html>
