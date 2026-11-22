{{-- Student portal (role "student": matching), inside the shared dashboard shell
     (public/css/bakr-dashboard.css — the same shell every BAKR dashboard uses).
     Menu: Dashboard, My matches, My profile, My matching form; Explore (collapsible); sidebar foot with Sign out.
     Scholars have their own accounts and their own layout (scholar.layout). --}}
@extends('layouts.app')

@section('page', 'student')
@section('no_navbar', '1')
@section('no_footer', '1')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/bakr-pages.css') }}">
    <link rel="stylesheet" href="{{ asset('css/student-portal.css') }}">
    <link rel="stylesheet" href="{{ asset('css/bakr-dashboard.css') }}">
@endpush

@section('content')
@php
    $uid = auth()->id();
    $me = auth()->user();
    $current = \App\Models\ScholarshipApplication::currentFor($uid)->first();
    $tally = $current ? $current->matches()->selectRaw('status, count(*) as n')->groupBy('status')->pluck('n', 'status') : collect();
    $nElig = (int) ($tally['eligible'] ?? 0);
    $nPoss = (int) ($tally['possible'] ?? 0);
    $nInfo = (int) ($tally['needs_info'] ?? 0);
    $nNot  = (int) ($tally['not_eligible'] ?? 0);
    $nAll  = $nElig + $nPoss + $nInfo + $nNot;
    $w     = fn ($n) => $nAll ? round($n / $nAll * 100, 1) : 0;
    $answers = $current?->answers ?? [];
    $initials = collect(preg_split('/\s+/', trim($me->name)))->filter()->take(2)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('');
    $banner = match (true) {
        request()->routeIs('student.results') => 'opt/students2.jpg',
        request()->routeIs('student.profile', 'student.profile.*') => 'opt/dnsc_gate.jpg',
        request()->routeIs('student.application.*') => 'opt/lib.jpg',
        default => 'dnsc_campus.jpg',
    };
    $home = route('student.dashboard');
@endphp

<div class="dash is-app sp-page sd">
    <div class="dash-panel{{ request()->routeIs('student.dashboard') ? ' is-dashboard' : '' }}">

        @include('partials.portal.mobilebar', ['home' => $home, 'portal' => 'Student Portal'])

        {{-- ============ SIDEBAR (drawer on phones) ============ --}}
        <aside class="dash-side is-native" id="dash-side" aria-label="Student menu">
            <button type="button" class="dash-side-close" aria-label="Close menu" data-dash-close><i class="bi bi-x-lg" aria-hidden="true"></i></button>
            <a class="dash-brand" href="{{ $home }}" aria-label="BAKR student dashboard">
                <img src="{{ asset('images/logo/bakr-logo.png') }}" alt="" width="30" height="30">
                <span>BAKR<small>Student Portal</small></span>
            </a>

            {{-- ============ STUDENT MENU (matching) ============ --}}
            <div class="dash-nav-label">Main</div>
            <nav class="dash-nav" aria-label="Student pages">
                {{-- Before the first submit these three need answers: they open the "finish your form" modal instead. --}}
                <a href="{{ route('student.dashboard') }}" @if (request()->routeIs('student.dashboard')) aria-current="page" @endif @unless ($current) data-open-dialog="finish-form" @endunless><i class="bi bi-grid-1x2" aria-hidden="true"></i><span class="dash-nav-text">Dashboard</span></a>
                <a href="{{ route('student.results') }}" @if (request()->routeIs('student.results')) aria-current="page" @endif @unless ($current) data-open-dialog="finish-form" @endunless><i class="bi bi-list-check" aria-hidden="true"></i><span class="dash-nav-text">My matches</span>@if ($nElig + $nPoss)<span class="dash-count" title="{{ $nElig + $nPoss }} that fit you"><span class="visually-hidden">, {{ $nElig + $nPoss }} that fit you</span><span aria-hidden="true">{{ $nElig + $nPoss }}</span></span>@endif</a>
                <a href="{{ route('student.profile') }}" @if (request()->routeIs('student.profile', 'student.profile.*')) aria-current="page" @endif @unless ($current) data-open-dialog="finish-form" @endunless><i class="bi bi-person-vcard" aria-hidden="true"></i><span class="dash-nav-text">My profile</span></a>
                <a href="{{ route('student.application.show') }}" @if (request()->routeIs('student.application.show')) aria-current="page" @endif><i class="bi bi-pencil-square" aria-hidden="true"></i><span class="dash-nav-text">My matching form</span></a>
            </nav>

            {{-- Explore: collapsible, like the scholar portal's; opens by itself on its own pages. --}}
            <details class="dash-nav-group" @if (request()->routeIs('student.help', 'student.scholarships')) open @endif>
                <summary>Explore <i class="bi bi-chevron-down dash-nav-chev" aria-hidden="true"></i></summary>
                <nav class="dash-nav" aria-label="Explore">
                    <a href="{{ route('student.scholarships') }}" @if (request()->routeIs('student.scholarships')) aria-current="page" @endif><i class="bi bi-mortarboard" aria-hidden="true"></i><span class="dash-nav-text">All scholarships</span></a>
                    <a href="{{ route('student.help') }}" @if (request()->routeIs('student.help')) aria-current="page" @endif><i class="bi bi-question-circle" aria-hidden="true"></i><span class="dash-nav-text">Help &amp; contact</span></a>
                    <a href="{{ route('student.help') }}#about" data-open-dialog="about-bakr"><i class="bi bi-info-circle" aria-hidden="true"></i><span class="dash-nav-text">About BAKR</span></a>
                    <a href="{{ url('/') }}"><i class="bi bi-house" aria-hidden="true"></i><span class="dash-nav-text">Public site</span></a>
                </nav>
            </details>

            @if ($current)
                <section class="sd-me dash-side-extra" aria-label="Match summary">
                    <div class="sd-big"><strong>{{ $nElig + $nPoss }}</strong><span>of {{ $nAll }} scholarships fit you so far</span></div>
                    <div class="sd-split" aria-hidden="true">
                        <i class="is-good" style="width: {{ $w($nElig) }}%"></i><i class="is-warn" style="width: {{ $w($nPoss) }}%"></i><i class="is-blue" style="width: {{ $w($nInfo) }}%"></i><i class="is-gray" style="width: {{ $w($nNot) }}%"></i>
                    </div>
                    <div class="sd-tiles">
                        <a href="{{ route('student.results', ['status' => 'eligible']) }}"><b class="g"><i class="bi bi-patch-check-fill"></i></b><strong>{{ $nElig }}</strong><span>Eligible</span></a>
                        <a href="{{ route('student.results', ['status' => 'possible']) }}"><b class="y"><i class="bi bi-hourglass-split"></i></b><strong>{{ $nPoss }}</strong><span>Nearly</span></a>
                        <a href="{{ route('student.results', ['status' => 'needs_info']) }}"><b class="b"><i class="bi bi-question-circle-fill"></i></b><strong>{{ $nInfo }}</strong><span>Need info</span></a>
                    </div>
                </section>
            @endif

            @include('partials.portal.side-foot', ['initials' => $initials ?: 'S', 'name' => $me->name, 'sub' => $answers['program_name'] ?? 'Student'])
        </aside>

        {{-- ============ MAIN ============ --}}
        <div class="dash-main">
            <header class="dash-hero">
                <img class="dash-hero-photo" src="{{ asset('images/img/'.$banner) }}" alt="" decoding="async">
                <div class="dash-hero-copy">
                    <ol class="bk-crumbs" aria-label="Breadcrumb">
                        <li><a href="{{ $home }}">Student portal</a></li>
                        <li aria-current="page">{{ trim(strtok(View::yieldContent('title'), '|')) }}</li>
                    </ol>
                    <h1>@yield('hero_title', 'My <em>BAKR</em>')</h1>
                    @hasSection('hero_sub')<p>@yield('hero_sub')</p>@endif
                    @hasSection('hero_actions')<div class="dash-hero-actions">@yield('hero_actions')</div>@endif
                </div>
                <div class="dash-hero-aside">
                    @include('partials.theme-toggle', ['class' => 'is-hero'])
                    {{-- Same three in every portal: About, Help, Sign out (everything else is in the sidebar). --}}
                    <a class="dash-icon-btn" href="{{ route('student.help') }}#about" data-open-dialog="about-bakr" aria-label="About BAKR" title="About BAKR"><i class="bi bi-info-lg" aria-hidden="true"></i></a>
                    <a class="dash-icon-btn" href="{{ route('student.help') }}" aria-label="Help and contact" title="Help and contact"><i class="bi bi-question-lg" aria-hidden="true"></i></a>
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

@include('partials.portal.about-dialog', ['audience' => 'student', 'helpRoute' => 'student.help'])
@include('partials.signout-dialog', ['logoutRoute' => 'student.logout'])

@unless ($current)
{{-- ============ "Finish your matching form first" (replaces the old banner) ============
     Opened by the locked sidebar links above, or automatically after the server sent the student
     back here from Dashboard / My matches / My profile (session 'form_first'). --}}
<dialog class="dash-dialog dash-confirm" id="finish-form" aria-labelledby="finish-form-title" @if (session('form_first')) data-auto-open @endif>
    <div class="dash-dialog-body">
        <span class="dash-confirm-icon is-info" aria-hidden="true"><i class="bi bi-clipboard-check"></i></span>
        <h2 id="finish-form-title">Finish your matching form first</h2>
        <p>
            @if (session('form_first'))<strong>{{ session('form_first') }}</strong> opens after you submit the form once.
            @else Your dashboard, matches and profile open after you submit the form once.
            @endif
            It takes about 5 minutes, and your answers stay private.
        </p>
    </div>
    <div class="dash-dialog-foot">
        <a class="dash-btn" href="{{ route('student.application.show') }}#appForm" data-close-dialog><i class="bi bi-pencil-square" aria-hidden="true"></i>Continue the form</a>
    </div>
</dialog>
@endunless
@endsection

@push('scripts')
    <script src="{{ asset('js/bakr-dashboard.js') }}"></script>
    <script src="{{ asset('js/student-ai.js') }}"></script>
@endpush
