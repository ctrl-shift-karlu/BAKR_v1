{{-- Admin portal shell: the same shared dashboard shell as every BAKR portal (public/css/bakr-dashboard.css). --}}
@php
    $openMessages = \App\Models\ContactMessage::whereNull('handled_at')->count();
    $nav = [
        ['admin.overview', 'bi-grid-1x2', 'Overview', null],
        ['admin.analytics', 'bi-bar-chart', 'Analytics', null],
        ['admin.accounts', 'bi-people', 'Accounts', null],
        ['admin.scholarships', 'bi-journal-richtext', 'Scholarships', null],
        ['admin.messages', 'bi-chat-left-text', 'Messages', $openMessages ?: null],
    ];
    $me = auth()->user();
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#143a2b">
    <title>@yield('title') | BAKR Admin Portal</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo/bakr-logo.png') }}">
    @include('partials.pwa-head')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Source+Serif+4:ital,opsz,wght@0,8..60,600;1,8..60,600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/bakr-provider.css') }}">
    <link rel="stylesheet" href="{{ asset('css/bakr-admin.css') }}">
    <link rel="stylesheet" href="{{ asset('css/bakr-dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/bakr-busy.css') }}">
    <link rel="stylesheet" href="{{ asset('css/bakr-dark.css') }}">
    <script src="{{ asset('js/bakr-theme.js') }}"></script>
</head>
<body class="dash is-app pv-body">
<div class="dash-panel">

    @include('partials.portal.mobilebar', ['home' => route('admin.overview'), 'portal' => 'Admin Portal'])

    <aside class="dash-side is-native" id="dash-side" aria-label="Admin menu">
        <button type="button" class="dash-side-close" aria-label="Close menu" data-dash-close><i class="bi bi-x-lg" aria-hidden="true"></i></button>
        <a class="dash-brand" href="{{ route('admin.overview') }}">
            <img src="{{ asset('images/logo/bakr-logo.png') }}" alt="">
            <span>BAKR<small>Admin Portal</small></span>
        </a>

        <div class="dash-nav-label">Main</div>
        <nav class="dash-nav" aria-label="Admin pages">
            @foreach ($nav as [$route, $icon, $label, $count])
                <a href="{{ route($route) }}" @if (request()->routeIs($route)) aria-current="page" @endif>
                    <i class="bi {{ $icon }}" aria-hidden="true"></i><span class="dash-nav-text">{{ $label }}</span>
                    @if ($count)<span class="dash-count" title="{{ $count }} open"><span class="visually-hidden">, {{ $count }} open</span><span aria-hidden="true">{{ $count }}</span></span>@endif
                </a>
            @endforeach
        </nav>

        {{-- A one-link group needs no collapsing: a plain section, like every portal's. --}}
        <div class="dash-nav-label">Explore</div>
        <nav class="dash-nav" aria-label="Explore">
            <a href="{{ url('/') }}" target="_blank" rel="noopener" title="Opens in a new tab"><i class="bi bi-box-arrow-up-right" aria-hidden="true"></i><span class="dash-nav-text">Public site</span></a>
        </nav>

        @include('partials.portal.side-foot', ['icon' => 'bi-shield-lock', 'name' => $me->name, 'sub' => 'Administrator'])
    </aside>

    <main class="dash-main">
        <header class="dash-hero">
            <img class="dash-hero-photo" src="{{ asset('images/img/'.trim(View::yieldContent('photo', 'dnsc_campus.jpg'))) }}" alt="" decoding="async">
            <div class="dash-hero-copy">
                <div class="dash-kicker">@yield('kicker', 'BAKR Admin Portal')</div>
                <h1>@yield('heading')</h1>
                @hasSection('sub')<p>@yield('sub')</p>@endif
            </div>
            <div class="dash-hero-aside">
                @include('partials.theme-toggle', ['class' => 'is-hero'])
                @include('partials.portal.hero-signout')
            </div>
        </header>

        @if (session('status'))
            <div class="dash-flash" role="status"><i class="bi bi-check-circle-fill" aria-hidden="true"></i><span>{{ session('status') }}</span></div>
        @endif
        @if (session('notice'))
            <div class="dash-flash is-warn" role="status"><i class="bi bi-info-circle-fill" aria-hidden="true"></i><span>{{ session('notice') }}</span></div>
        @endif
        @include('partials.portal.sent-dialog')
        @if ($errors->any())
            <div class="dash-flash is-error" role="alert"><i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i>
                <ul>@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        @yield('content')
    </main>
</div>

@include('partials.signout-dialog', ['logoutRoute' => 'admin.logout'])

<script src="{{ asset('js/bakr-dashboard.js') }}"></script>
<script src="{{ asset('js/bakr-busy.js') }}" defer></script>
@stack('scripts')
</body>
</html>
