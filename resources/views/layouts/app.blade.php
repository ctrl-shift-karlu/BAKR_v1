<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#143a2b">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="{{ asset('js/bakr-theme.js') }}"></script>
    <meta name="description" content="@yield('description', 'BAKR is an academic prototype scholarship guide for Davao del Norte State College students.')">
    <title>@yield('title', config('bakr.system_name'))</title>
    <script>document.documentElement.classList.add('js-reveal');try{if(sessionStorage.getItem('bakrIntroSeen')==='1')document.documentElement.classList.add('bk-intro-seen');}catch(e){}</script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Source+Serif+4:opsz,wght@8..60,500;8..60,600;8..60,700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="icon" type="image/png" href="{{ asset('images/logo/bakr-logo.png') }}">
    @include('partials.pwa-head')
    <link rel="stylesheet" href="{{ asset('css/bakr-core.css') }}">
    <link rel="stylesheet" href="{{ asset('css/bakr-layout.css') }}">
    @php($navSignout = ! View::hasSection('no_navbar') && in_array(auth()->user()?->role, ['student', 'scholar', 'provider', 'admin'], true))
    @if ($navSignout)
        {{-- Signed in on a public page: the navbar's Sign out uses the portals' confirmation dialog (its styles live in the dashboard sheet). --}}
        <link rel="stylesheet" href="{{ asset('css/bakr-dashboard.css') }}">
    @endif
    @stack('styles')
    <link rel="stylesheet" href="{{ asset('css/bakr-busy.css') }}">
    <link rel="stylesheet" href="{{ asset('css/bakr-dark.css') }}">
</head>
<body data-page="@yield('page')">

<a class="nx-skip visually-hidden-focusable" href="#main">Skip to main content</a>

@stack('preloader')

@unless(View::hasSection('no_navbar'))
@include('partials.navbar')
@endunless

<main id="main" tabindex="-1">
    @yield('content')
</main>

@unless(View::hasSection('no_footer'))
@include('partials.footer')
@endunless

@stack('modals')

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="{{ asset('js/bakr-core.js') }}"></script>
<script src="{{ asset('js/bakr-busy.js') }}" defer></script>
@if ($navSignout)<script src="{{ asset('js/bakr-dashboard.js') }}"></script>@endif
@stack('scripts')
</body>
</html>
