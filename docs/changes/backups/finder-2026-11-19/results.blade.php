@extends('layouts.app')

{{-- Scholarship Finder results. Built in memory from the visitor's answers; nothing was saved. --}}

@section('title', 'Your matches | Scholarship Finder | BAKR')
@section('page', 'finder')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/bakr-pages.css') }}">
    <link rel="stylesheet" href="{{ asset('css/student-portal.css') }}">
@endpush

@php
    $icons = ['pass' => 'bi-check-circle-fill', 'fail' => 'bi-x-circle-fill', 'confirm' => 'bi-question-circle-fill', 'unknown' => 'bi-dash-circle-fill'];
    $labels = \App\Models\ScholarshipMatch::STATUS_LABELS;
    $fit = $counts['eligible'] + $counts['possible'];
@endphp

@section('content')
<header class="bk-page-hero" style="--page-hero-image: url('{{ asset('images/img/graduates.jpg') }}')">
    <div class="container">
        <ol class="bk-crumbs" aria-label="Breadcrumb">
            <li><a href="{{ route('home') }}">Home</a></li>
            <li><a href="{{ route('finder') }}">Scholarship Finder</a></li>
            <li aria-current="page">Results</li>
        </ol>
        <h1>{{ $fit }} of {{ $total }} scholarships <em>may fit you</em></h1>
        <p>These results use the same rules as BAKR's student portal. They are a guide, not an award: the provider or the DNSC Scholarship and Grants Unit makes the final decision.</p>
    </div>
</header>

<div class="container finder-page">
    <section class="sp-card finder-cta" aria-label="Save these results">
        <div>
            <h2><i class="bi bi-bookmark-star" aria-hidden="true"></i> Want to keep these results and apply?</h2>
            <p class="sp-sub">Nothing was saved. With a free student account BAKR remembers your answers and updates your matches when scholarships change.</p>
        </div>
        <div class="finder-cta-actions">
            @if (auth()->user()?->role === 'student')
                <a class="sp-btn sp-btn-gold" href="{{ route('find') }}"><i class="bi bi-bookmark-check" aria-hidden="true"></i> Open my saved matches</a>
            @else
                <a class="sp-btn sp-btn-gold" href="{{ route('student.register') }}"><i class="bi bi-person-plus"></i> Create a free account</a>
                <a class="sp-btn sp-btn-line" href="{{ route('student.login') }}">I have an account</a>
            @endif
            <a class="sp-btn sp-btn-line" href="{{ route('finder') }}"><i class="bi bi-arrow-counterclockwise"></i> Start over</a>
        </div>
    </section>

    @if ($matches->isEmpty())
        @include('partials.no-match', ['catalogueUrl' => route('scholarships'), 'answersUrl' => route('finder'), 'answersLabel' => 'Start over'])
    @else
    <nav class="sp-filters" aria-label="Results by status">
        @foreach ($counts as $status => $n)
            <a class="sp-filter" href="#group-{{ $status }}">{{ $labels[$status] }}<span>{{ $n }}</span></a>
        @endforeach
    </nav>
    @endif

    @foreach (\App\Models\ScholarshipMatch::LISTED as $status)
        @php $group = $matches->where('status', $status); @endphp
        @continue($group->isEmpty())
        <h2 class="finder-group" id="group-{{ $status }}">{{ $labels[$status] }} <span>{{ $group->count() }}</span></h2>

        @foreach ($group as $m)
            @php
                $s = $m->scholarship;
                $shown = collect($m->checks)->where('result', '!=', 'na');
            @endphp
            <article class="sp-card sp-match" aria-labelledby="title-{{ $s->slug }}">
                <div class="sp-match-head">
                    <div>
                        <span class="sp-sector">{{ $s->sectorLabel() }}</span>
                        <h3 id="title-{{ $s->slug }}">{{ $s->name }}</h3>
                        <span class="sp-pill {{ $m->status }}">{{ $m->statusLabel() }}</span>
                        <p class="sp-lead finder-lead">{{ $s->overview }}</p>
                    </div>
                </div>

                <div class="sp-ai">
                    <span class="sp-ai-tag"><i class="bi bi-chat-left-text" aria-hidden="true"></i> Quick explanation</span>
                    <p>{{ $explainer->quick($m) }}</p>
                </div>

                <details class="sp-details sp-checks-toggle" @if ($m->status !== 'not_eligible') open @endif>
                    <summary><i class="bi bi-list-check" aria-hidden="true"></i> {{ $shown->count() }} requirement checks</summary>
                    <ul class="sp-checks" aria-label="Requirement checks">
                        @foreach ($shown as $c)
                            <li class="sp-check {{ $c['result'] }}">
                                <i class="bi {{ $icons[$c['result']] ?? 'bi-dash-circle-fill' }}" aria-hidden="true"></i>
                                <div>{{ $c['label'] }}@if (! $c['required'])<span class="opt">optional</span>@endif
                                    @if ($c['detail'])<small>{{ $c['detail'] }}</small>@endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </details>

                <div class="sp-match-foot">
                    @if ($s->official_url)
                        <a class="sp-btn sp-btn-line sp-btn-sm" href="{{ $s->official_url }}" target="_blank" rel="noopener noreferrer"><i class="bi bi-box-arrow-up-right"></i> Official information</a>
                    @endif
                    <a class="sp-btn sp-btn-line sp-btn-sm" href="{{ route('scholarships') }}#card-{{ $s->slug }}"><i class="bi bi-journal-text"></i> Full details</a>
                </div>
            </article>
        @endforeach
    @endforeach
</div>
@endsection
