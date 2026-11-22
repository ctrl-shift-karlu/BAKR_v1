@extends('student.layout')
@section('title', 'Version '.$saved->version.' of your answers | BAKR')

@section('hero_title')
Version <em>{{ $saved->version }}</em> of your answers
@endsection
@section('hero_sub')
What you answered on {{ $saved->submitted_at->format('M j, Y') }}. Your matches use your current answers (version {{ $current->version }}).
@endsection

@section('portal')
{{-- One EARLIER saved version (StudentDashboardController::version, own versions only). Answers that are
     different now are marked; "Use these answers again" opens the matching form filled in with them. --}}
<section class="sp-card sp-prof-head" aria-label="About this version">
    <div class="sp-prof-id">
        <div class="sp-avatar" aria-hidden="true"><i class="bi bi-clock-history"></i></div>
        <div class="sp-prof-name">
            <strong>Version {{ $saved->version }}</strong>
            <span>Saved {{ $saved->submitted_at->format('M j, Y g:i A') }}</span>
        </div>
    </div>
    <ul class="sp-prof-chips" aria-label="Compared with your current answers">
        <li><i class="bi bi-arrow-left-right" aria-hidden="true"></i>
            @if (count($changed) === 0)
                Same answers as your current version {{ $current->version }}
            @else
                {{ count($changed) }} {{ str('answer')->plural(count($changed)) }} different from your current version {{ $current->version }}
            @endif
        </li>
    </ul>
    <div class="sp-prof-actions">
        @if (count($changed) > 0)
            <a class="sp-btn sp-btn-gold sp-btn-sm" href="{{ route('student.application.show', ['version' => $saved->version]) }}"><i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i> Use these answers again</a>
        @endif
        <a class="sp-btn sp-btn-line sp-btn-sm" href="{{ route('student.profile') }}"><i class="bi bi-arrow-left" aria-hidden="true"></i> Back to my profile</a>
    </div>
</section>

<section class="sp-card">
    <h2>Your answers in version {{ $saved->version }}</h2>
    <p class="sp-sub">
        @if (count($changed) > 0)
            Answers marked <strong>Different now</strong> show what you answer today. "Use these answers again" opens the matching form filled in with this version, so you can check it and submit it as a new version.
        @else
            Nothing here is different from your current answers.
        @endif
    </p>
    @include('student.partials.answers', ['answers' => $saved->answers, 'sections' => $sections, 'changed' => $changed, 'compare' => $current->answers])
</section>

<div class="sp-grid-2">
    @include('student.partials.versions', ['history' => $history, 'viewing' => (int) $saved->version])
    <div class="sp-note"><i class="bi bi-shield-lock-fill"></i>
        <span>Earlier versions are kept encrypted, and only you can see them. Only your current version is used for your matches and the anonymous totals on the About page.</span></div>
</div>
@endsection
