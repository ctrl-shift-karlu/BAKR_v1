@extends('layouts.app')

{{-- Public Scholarship Finder. Same questions as the student questionnaire (ApplicationSchema) and the
     same rule engine, so results match what a registered student sees. Nothing is saved.
     The previous standalone version is in docs/changes/backups/scholarshipFinder.blade.php.2026-11-13.bak --}}

@section('title', 'Scholarship Finder | BAKR')
@section('description', 'Answer a few questions and see which DNSC, government and private scholarships may fit you. Nothing is saved.')
@section('page', 'finder')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/bakr-pages.css') }}">
    <link rel="stylesheet" href="{{ asset('css/student-portal.css') }}">
@endpush

@section('content')
<header class="bk-page-hero" style="--page-hero-image: url('{{ asset('images/img/graduates.jpg') }}')">
    <div class="container">
        <ol class="bk-crumbs" aria-label="Breadcrumb">
            <li><a href="{{ route('home') }}">Home</a></li>
            <li aria-current="page">Scholarship Finder</li>
        </ol>
        <h1>Find scholarships that <em>fit you</em></h1>
        <p>Answer the questions below and BAKR checks them against every scholarship's published requirements, the same way it does for registered students. Nothing you enter here is saved.</p>
    </div>
</header>

<div class="container finder-page">
    @if (auth()->user()?->role === 'student')
        <div class="sp-note"><i class="bi bi-person-check" aria-hidden="true"></i>
            <span>You're signed in. Your saved answers and matches are in your portal: <a href="{{ route('find') }}">open my matches</a>.</span></div>
    @else
        <div class="sp-note"><i class="bi bi-shield-check" aria-hidden="true"></i>
            <span><strong>Private by design.</strong> Your answers are only used to show your results on the next page and are not stored. Create a free account if you want BAKR to remember them.</span></div>
    @endif

    @if ($errors->any())
        <div class="sp-flash err" role="alert"><i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i>
            <span>Some answers need your attention. They are marked in red below.</span></div>
    @endif

    <form method="POST" action="{{ route('finder.check') }}" id="appForm" novalidate>
        @csrf

        <div class="sp-stepbar">
            <ol class="sp-steps" aria-label="Sections of this form" id="stepper">
                @foreach (array_values($sections) as $i => $section)
                    <li><button type="button" data-goto="{{ $i }}" aria-current="{{ $i === 0 ? 'step' : 'false' }}"><b>{{ $i + 1 }}</b><span>{{ $section['title'] }}</span></button></li>
                @endforeach
            </ol>
            <div class="sp-bar" aria-hidden="true"><span id="progress"></span></div>
        </div>

        @foreach (array_values($sections) as $i => $section)
            <section class="sp-card sp-section" data-step="{{ $i }}" id="section-{{ $i }}" aria-labelledby="sec-{{ $i }}">
                <h2 id="sec-{{ $i }}"><i class="bi {{ $section['icon'] }}" aria-hidden="true"></i> {{ $section['title'] }}</h2>
                <p class="sp-sub">{{ $i === 0 ? 'We never ask for your name here. Only what scholarships check.' : $section['intro'] }}</p>
                <div class="sp-fields">
                    @foreach ($section['fields'] as $key => $f)
                        @include('student.partials.field', ['key' => $key, 'f' => $f, 'saved' => $saved])
                    @endforeach
                </div>
            </section>
        @endforeach

        <section class="sp-card sp-finish">
            <div class="sp-finish-row">
                <p id="progressText" class="sp-progress-text" aria-live="polite"></p>
                <button type="submit" class="sp-btn sp-btn-gold"><i class="bi bi-search" aria-hidden="true"></i> Show my matches</button>
            </div>
        </section>
    </form>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/student-form.js') }}"></script>
@endpush
