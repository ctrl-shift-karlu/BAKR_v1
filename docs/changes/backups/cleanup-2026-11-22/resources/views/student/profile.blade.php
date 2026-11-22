@extends('student.layout')
@section('title', 'My profile | BAKR')

@section('hero_title')
My <em>profile</em>
@endsection
@section('hero_sub')
What BAKR used to find your matches. Only you can see this page.
@endsection

@section('portal')
{{-- My profile: who you are, then your saved answers grouped by part of the form, then your saved versions.
     Only the signed-in student's own current application is shown (StudentDashboardController::profile). --}}
@php $answers = $application->answers; @endphp
<section class="sp-card sp-prof-head" aria-label="Your account">
    <div class="sp-prof-id">
        <div class="sp-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</div>
        <div class="sp-prof-name">
            <strong>{{ $user->name }}</strong>
            <span>{{ $user->email }}</span>
        </div>
    </div>
    <ul class="sp-prof-chips" aria-label="About this saved form">
        <li><i class="bi bi-hash" aria-hidden="true"></i>{{ $application->referenceNumber() }}</li>
        <li><i class="bi bi-layers" aria-hidden="true"></i>Version {{ $application->version }}</li>
        <li><i class="bi bi-clock-history" aria-hidden="true"></i>Saved {{ $application->submitted_at->format('M j, Y g:i A') }}</li>
    </ul>
    <div class="sp-prof-actions">
        <a class="sp-btn sp-btn-gold sp-btn-sm" href="{{ route('student.application.show') }}"><i class="bi bi-pencil-square" aria-hidden="true"></i> Change my answers</a>
        <a class="sp-btn sp-btn-line sp-btn-sm" href="{{ route('student.application.print') }}"><i class="bi bi-printer" aria-hidden="true"></i> Print my form</a>
    </div>
</section>

<section class="sp-card">
    <h2>Your answers</h2>
    <p class="sp-sub">This is what BAKR used to find your matches. Only you can see this page.</p>
    @include('student.partials.answers', ['answers' => $answers, 'sections' => $sections])
</section>

<div class="sp-grid-2" id="versions">
    @include('student.partials.versions', ['history' => $history])
    <div class="sp-note"><i class="bi bi-shield-lock-fill"></i>
        <span>Your answers are encrypted, and only you can see them. The public About page shows anonymous totals only, and any group of fewer than 10 students is hidden. BAKR never asks you to upload documents.</span></div>
</div>
@endsection


