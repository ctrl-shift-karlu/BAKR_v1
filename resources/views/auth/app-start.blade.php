{{-- App start page (AppStartController, /app = the installed app's start_url) for someone not
     signed in: pick a portal. Signed-in people never see it (they go straight to their portal).
     Styles: public/css/bakr-pwa.css (.pwa-start*). The admin login is deliberately not listed. --}}
@extends('layouts.auth')

@section('title', 'Welcome')
@section('body', 'is-student')
@section('portal', config('bakr.tagline'))

@section('heading', 'Welcome to BAKR')
@section('help', 'Choose how you use BAKR, then sign in.')

@section('card')
    <nav class="pwa-start" aria-label="Choose your portal">
        <a class="pwa-start-link" href="{{ route('student.login') }}">
            <i class="bi bi-search" aria-hidden="true"></i>
            <span><strong>I'm a student</strong><small>Find the scholarships you qualify for</small></span>
            <i class="bi bi-chevron-right" aria-hidden="true"></i>
        </a>
        <a class="pwa-start-link" href="{{ route('scholar.login') }}">
            <i class="bi bi-mortarboard" aria-hidden="true"></i>
            <span><strong>I'm a scholar</strong><small>Follow your scholarship, stipends and documents</small></span>
            <i class="bi bi-chevron-right" aria-hidden="true"></i>
        </a>
        <a class="pwa-start-link" href="{{ route('provider.login') }}">
            <i class="bi bi-building" aria-hidden="true"></i>
            <span><strong>I'm a provider</strong><small>Manage your scholars and scholarships</small></span>
            <i class="bi bi-chevron-right" aria-hidden="true"></i>
        </a>
    </nav>

    @include('partials.pwa-install', ['class' => 'pwa-start-install'])
@endsection

@section('foot')
    <p>New student? <a href="{{ route('student.register') }}">Create an account</a></p>
    <p><a href="{{ route('scholarships') }}">Browse the scholarships</a> without signing in.</p>
@endsection
