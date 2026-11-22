{{-- Scholar portal (role "scholar", signed in at /scholarLogin): monitoring only, no matching.
     Same shared dashboard shell as every BAKR portal (public/css/bakr-dashboard.css). Menu, after the
     scholar prototype (scholarDashboardF): Main (Overview, My scholarship, Requirements), Account
     (Profile, Settings), Explore ▸ (collapsible). Sign out: sidebar foot + hero, like every portal
     (Settings also has a Sign out card); all open the same red confirmation. --}}
@extends('layouts.app')

@section('page', 'scholar')
@section('no_navbar', '1')
@section('no_footer', '1')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/bakr-pages.css') }}">
    <link rel="stylesheet" href="{{ asset('css/student-portal.css') }}">
    <link rel="stylesheet" href="{{ asset('css/bakr-provider.css') }}">
    <link rel="stylesheet" href="{{ asset('css/bakr-dashboard.css') }}">
@endpush

@section('content')
@php
    $me = auth()->user();
    $initials = collect(preg_split('/\s+/', trim($me->name)))->filter()->take(2)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('');
    $myScholars = \App\Models\Scholar::where('user_id', $me->id)->with('scholarship')->orderBy('id')->get();
    $term = \App\Support\AcademicTerm::current();
    // Sidebar badge: this semester's requirements still to upload or to fix.
    $todo = \App\Models\ScholarRequirement::whereIn('scholar_id', $myScholars->filter(fn ($s) => $s->scholarship?->offers('requirements'))->pluck('id'))
        ->where('term', $term)->whereIn('status', ['missing', 'needs_revision'])->owed()->count();
    // Sidebar badge: updates from providers not seen yet (Overview › Updates marks them read).
    $newUpdates = $me->unreadNotifications()->count();
    $exploreOpen =request()->routeIs('scholar.scholarships', 'scholar.help');
    $home = route('scholar.overview');
    $banner = match (true) {
        request()->routeIs('scholar.profile', 'scholar.settings') => 'opt/dnsc_gate.jpg',
        request()->routeIs('scholar.requirements') => 'opt/lib.jpg',
        request()->routeIs('scholar.scholarship') => 'graduates.jpg',
        default => 'dnsc_campus.jpg',
    };
@endphp

<div class="dash is-app sp-page sd">
    <div class="dash-panel">

        @include('partials.portal.mobilebar', ['home' => $home, 'portal' => 'Scholar Portal'])

        {{-- ============ SIDEBAR (drawer on phones) ============ --}}
        <aside class="dash-side is-native" id="dash-side" aria-label="Scholar menu">
            <button type="button" class="dash-side-close" aria-label="Close menu" data-dash-close><i class="bi bi-x-lg" aria-hidden="true"></i></button>
            <a class="dash-brand" href="{{ $home }}" aria-label="BAKR scholar overview">
                <img src="{{ asset('images/logo/bakr-logo.png') }}" alt="" width="30" height="30">
                <span>BAKR<small>Scholar Portal</small></span>
            </a>

            <div class="dash-nav-label">Main</div>
            <nav class="dash-nav" aria-label="Scholar pages">
                <a href="{{ route('scholar.overview') }}" @if (request()->routeIs('scholar.overview')) aria-current="page" @endif><i class="bi bi-grid-1x2" aria-hidden="true"></i><span class="dash-nav-text">Overview</span>@if ($newUpdates)<span class="dash-count" title="{{ $newUpdates }} new {{ \Illuminate\Support\Str::plural('update', $newUpdates) }}"><span class="visually-hidden">, {{ $newUpdates }} new {{ \Illuminate\Support\Str::plural('update', $newUpdates) }}</span><span aria-hidden="true">{{ $newUpdates }}</span></span>@endif</a>
                <a href="{{ route('scholar.scholarship') }}" @if (request()->routeIs('scholar.scholarship')) aria-current="page" @endif><i class="bi bi-award" aria-hidden="true"></i><span class="dash-nav-text">My scholarship</span></a>
                <a href="{{ route('scholar.requirements') }}" @if (request()->routeIs('scholar.requirements')) aria-current="page" @endif><i class="bi bi-folder-check" aria-hidden="true"></i><span class="dash-nav-text">Requirements</span>@if ($todo)<span class="dash-count" title="{{ $todo }} to do"><span class="visually-hidden">, {{ $todo }} to do</span><span aria-hidden="true">{{ $todo }}</span></span>@endif</a>
            </nav>

            <div class="dash-nav-label">Account</div>
            <nav class="dash-nav" aria-label="Account">
                <a href="{{ route('scholar.profile') }}" @if (request()->routeIs('scholar.profile')) aria-current="page" @endif><i class="bi bi-person-circle" aria-hidden="true"></i><span class="dash-nav-text">Profile</span></a>
                <a href="{{ route('scholar.settings') }}" @if (request()->routeIs('scholar.settings')) aria-current="page" @endif><i class="bi bi-gear" aria-hidden="true"></i><span class="dash-nav-text">Settings</span></a>
            </nav>

            {{-- Explore: collapsible (same as the student portal); last, so the everyday pages come first. --}}
            <details class="dash-nav-group" @if ($exploreOpen) open @endif>
                <summary>Explore <i class="bi bi-chevron-down dash-nav-chev" aria-hidden="true"></i></summary>
                <nav class="dash-nav" aria-label="Explore">
                    <a href="{{ route('scholar.scholarships') }}" @if (request()->routeIs('scholar.scholarships')) aria-current="page" @endif><i class="bi bi-mortarboard" aria-hidden="true"></i><span class="dash-nav-text">All scholarships</span></a>
                    <a href="{{ route('scholar.help') }}" @if (request()->routeIs('scholar.help')) aria-current="page" @endif><i class="bi bi-question-circle" aria-hidden="true"></i><span class="dash-nav-text">Help &amp; contact</span></a>
                    <a href="{{ route('scholar.help') }}#about" data-open-dialog="about-bakr"><i class="bi bi-info-circle" aria-hidden="true"></i><span class="dash-nav-text">About BAKR</span></a>
                    <a href="{{ url('/') }}"><i class="bi bi-house" aria-hidden="true"></i><span class="dash-nav-text">Public site</span></a>
                </nav>
            </details>

            @include('partials.portal.side-foot', ['initials' => $initials ?: 'S', 'name' => $me->name, 'sub' => ($myScholars->first()?->scholarship?->shortName() ?? 'BAKR').' scholar'])
        </aside>

        {{-- ============ MAIN ============ --}}
        <div class="dash-main">
            <header class="dash-hero">
                <img class="dash-hero-photo" src="{{ asset('images/img/'.$banner) }}" alt="" decoding="async">
                <div class="dash-hero-copy">
                    <ol class="bk-crumbs" aria-label="Breadcrumb">
                        <li><a href="{{ $home }}">Scholar portal</a></li>
                        <li aria-current="page">{{ trim(strtok(View::yieldContent('title'), '|')) }}</li>
                    </ol>
                    <h1>@yield('hero_title', 'My <em>scholarship</em>')</h1>
                    @hasSection('hero_sub')<p>@yield('hero_sub')</p>@endif
                    @hasSection('hero_actions')<div class="dash-hero-actions">@yield('hero_actions')</div>@endif
                </div>
                <div class="dash-hero-aside">
                    @include('partials.theme-toggle', ['class' => 'is-hero'])
                    {{-- Same three in every portal: About, Help, Sign out (everything else is in the sidebar). --}}
                    <a class="dash-icon-btn" href="{{ route('scholar.help') }}#about" data-open-dialog="about-bakr" aria-label="About BAKR" title="About BAKR"><i class="bi bi-info-lg" aria-hidden="true"></i></a>
                    <a class="dash-icon-btn" href="{{ route('scholar.help') }}" aria-label="Help and contact" title="Help and contact"><i class="bi bi-question-lg" aria-hidden="true"></i></a>
                    @include('partials.portal.hero-signout')
                </div>
            </header>

            <div class="sp-main" id="portal">
                @if (session('status'))
                    <div class="sp-flash ok" role="status"><i class="bi bi-check-circle-fill" aria-hidden="true"></i> {{ session('status') }}</div>
                @endif
                @if (session('notice'))
                    <div class="sp-flash warn" role="status"><i class="bi bi-info-circle-fill" aria-hidden="true"></i> {{ session('notice') }}</div>
                @endif
                @include('partials.portal.sent-dialog')
                @yield('portal')
            </div>
        </div>
    </div>
</div>

@include('partials.portal.about-dialog', ['audience' => 'scholar', 'helpRoute' => 'scholar.help'])
@include('partials.signout-dialog', ['logoutRoute' => 'scholar.logout'])
@endsection

@push('scripts')
    <script src="{{ asset('js/bakr-dashboard.js') }}"></script>
@endpush
