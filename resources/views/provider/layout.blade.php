{{-- Provider Portal shell. Every provider page extends this; the shell itself comes from
     public/css/bakr-dashboard.css (shared by every BAKR dashboard).
     Disbursements / Requirements appear only for providers that use them ($scope->has()). --}}
@php
    $nav = [
        // [route, icon, label, badge count, what the badge counts]
        'Main' => [
            ['provider.dashboard', 'bi-grid-1x2', 'Overview', null, null],
            ['provider.scholars', 'bi-mortarboard', 'Scholars', $navCounts['scholars'] ?: null, 'inactive'],
            // Correction requests from your own scholars (never the admin's inbox).
            ['provider.messages', 'bi-chat-left-text', 'Messages', ($navCounts['messages'] ?? 0) ?: null, 'open'],
        ],
        'Operations' => array_values(array_filter([
            $scope->has('disbursements') ? ['provider.disbursements', 'bi-cash-coin', 'Disbursements', $navCounts['disbursements'] ?: null, 'not released this semester'] : null,
            $scope->has('requirements') ? ['provider.requirements', 'bi-folder-check', 'Requirements', $navCounts['requirements'] ?: null, 'to review'] : null,
        ])),
        'Manage' => [
            ['provider.scholarships', 'bi-journal-richtext', 'My scholarships', null, null],
        ],
    ];
    $nav = array_filter($nav);
    $moreOpen = request()->routeIs('provider.help');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#143a2b">
    <title>@yield('title') | {{ $provider['name'] }} | BAKR Provider Portal</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo/bakr-logo.png') }}">
    @include('partials.pwa-head')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Source+Serif+4:ital,opsz,wght@0,8..60,600;1,8..60,600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/bakr-provider.css') }}">
    <link rel="stylesheet" href="{{ asset('css/bakr-dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/bakr-busy.css') }}">
    <link rel="stylesheet" href="{{ asset('css/bakr-dark.css') }}">
    <script src="{{ asset('js/bakr-theme.js') }}"></script>
</head>
<body class="dash is-app pv-body">
<div class="dash-panel">

    @include('partials.portal.mobilebar', ['home' => route('provider.dashboard'), 'portal' => 'Provider Portal'])

    <aside class="dash-side is-native" id="dash-side" aria-label="Provider menu">
        <button type="button" class="dash-side-close" aria-label="Close menu" data-dash-close><i class="bi bi-x-lg" aria-hidden="true"></i></button>
        <a class="dash-brand" href="{{ route('provider.dashboard') }}">
            <img src="{{ asset('images/logo/bakr-logo.png') }}" alt="">
            <span>BAKR<small>Provider Portal</small></span>
        </a>

        @foreach ($nav as $group => $items)
            <div class="dash-nav-label">{{ $group }}</div>
            <nav class="dash-nav" aria-label="{{ $group }}">
                @foreach ($items as [$route, $icon, $label, $count, $countLabel])
                    <a href="{{ route($route) }}" @if (request()->routeIs($route, $route.'.*')) aria-current="page" @endif>
                        <i class="bi {{ $icon }}" aria-hidden="true"></i><span class="dash-nav-text">{{ $label }}</span>
                        @if ($count)<span class="dash-count" title="{{ $count }} {{ $countLabel }}"><span class="visually-hidden">, {{ $count }} {{ $countLabel }}</span><span aria-hidden="true">{{ $count }}</span></span>@endif
                    </a>
                @endforeach
            </nav>
        @endforeach

        {{-- Help & info: collapsible; opens by itself on the Help page. Sign out is NOT in here (dash-side-foot). --}}
        <details class="dash-nav-group" @if ($moreOpen) open @endif>
            <summary>Help &amp; info <i class="bi bi-chevron-down dash-nav-chev" aria-hidden="true"></i></summary>
            <nav class="dash-nav" aria-label="Help and info">
                <a href="{{ route('provider.help') }}" @if (request()->routeIs('provider.help')) aria-current="page" @endif><i class="bi bi-question-circle" aria-hidden="true"></i><span class="dash-nav-text">Help &amp; contact</span></a>
                <a href="{{ route('provider.help') }}#about" data-open-dialog="about-bakr"><i class="bi bi-info-circle" aria-hidden="true"></i><span class="dash-nav-text">About BAKR</span></a>
                {{-- Your own official website (config/providers.php 'website'), not BAKR's public page (owner, 2026-11-21). --}}
                @if ($scope->website())
                    <a href="{{ $scope->website() }}" target="_blank" rel="noopener noreferrer" title="Opens {{ $provider['name'] }}'s official website in a new tab"><i class="bi bi-box-arrow-up-right" aria-hidden="true"></i><span class="dash-nav-text">Official website</span><span class="visually-hidden"> (opens in a new tab)</span></a>
                @endif
            </nav>
        </details>

        @include('partials.portal.side-foot', ['logo' => $provider['logo'], 'name' => $provider['name'], 'sub' => 'Scholarship provider'])
    </aside>

    <main class="dash-main">
        {{-- Photo hero: each page picks a campus photo with @section('photo'). --}}
        <header class="dash-hero">
            <img class="dash-hero-photo" src="{{ asset('images/img/'.trim(View::yieldContent('photo', 'dnsc_campus.jpg'))) }}" alt="" decoding="async">
            <div class="dash-hero-copy">
                <div class="dash-kicker">@yield('kicker', $provider['name'])</div>
                <h1>@yield('heading')</h1>
                @hasSection('sub')<p>@yield('sub')</p>@endif
                @hasSection('actions')<div class="dash-hero-actions">@yield('actions')</div>@endif
            </div>
            <div class="dash-hero-aside">
                @include('partials.theme-toggle', ['class' => 'is-hero'])
                <a class="dash-icon-btn" href="{{ route('provider.help') }}#about" data-open-dialog="about-bakr" aria-label="About BAKR" title="About BAKR"><i class="bi bi-info-lg" aria-hidden="true"></i></a>
                <a class="dash-icon-btn" href="{{ route('provider.help') }}" aria-label="Help and contact" title="Help and contact"><i class="bi bi-question-lg" aria-hidden="true"></i></a>
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

@include('partials.portal.about-dialog', ['audience' => 'provider', 'helpRoute' => 'provider.help'])
@include('partials.signout-dialog', ['logoutRoute' => 'provider.logout'])
@include('provider.partials.file-preview')

<script src="{{ asset('js/bakr-provider.js') }}"></script>
<script src="{{ asset('js/bakr-dashboard.js') }}"></script>
<script src="{{ asset('js/bakr-busy.js') }}" defer></script>
</body>
</html>
