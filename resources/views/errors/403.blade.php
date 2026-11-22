{{-- 403 page. The status stays 403 (access is still refused); this only explains why and
     offers the way out, e.g. a provider who opens a student page while still signed in. --}}
@extends('layouts.app')

@php
    $user = auth()->user();
    $role = $user?->role;
    $home = match ($role) {
        'student' => ['student.dashboard', 'Go to my student dashboard'],
        'scholar' => ['scholar.overview', 'Go to my scholar portal'],
        'provider' => ['provider.dashboard', 'Go to my provider dashboard'],
        'admin' => ['admin.overview', 'Go to the admin portal'],
        default => null,
    };
    $logout = match ($role) {
        'student' => 'student.logout',
        'scholar' => 'scholar.logout',
        'provider' => 'provider.logout',
        'admin' => 'admin.logout',
        default => null,
    };
    $roleLabel = ['student' => 'a student account', 'scholar' => 'a scholar account', 'provider' => 'a scholarship provider account', 'admin' => 'the administrator account'][$role] ?? null;
    $wantsStudentArea = request()->is('student/*');
    $wantsScholarArea = request()->is('scholar', 'scholar/*');
@endphp

@section('title', 'No access | BAKR')

@section('content')
<section class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 text-center">
                <div class="display-6 mb-3" aria-hidden="true"><i class="bi bi-shield-lock"></i></div>
                <h1 class="h3 fw-bold mb-3">This page isn't available for your account</h1>

                @if ($roleLabel)
                    <p class="mb-2">You're signed in with <strong>{{ $roleLabel }}</strong>@if ($role === 'provider') ({{ $user->name }})@endif.</p>
                    @if ($wantsStudentArea && $role === 'scholar')
                        <p class="text-secondary mb-4">Scholarship matching is for student accounts. Your scholar account has its own portal: your scholarship, stipends and requirements.</p>
                    @elseif ($wantsStudentArea && $role !== 'student')
                        <p class="text-secondary mb-4">The matching form and the student pages are only for student accounts. Sign out first, then sign in or sign up as a student.</p>
                    @elseif ($wantsScholarArea && $role !== 'scholar')
                        <p class="text-secondary mb-4">The scholar portal is for scholars. Your provider creates your scholar account when they add you. Sign out first, then use the Scholar login.</p>
                    @elseif (request()->is('provider/*') && $role !== 'provider')
                        <p class="text-secondary mb-4">This page is for scholarship providers only. Sign out first, then use your provider login.</p>
                    @else
                        <p class="text-secondary mb-4">Your account doesn't have access to this page.</p>
                    @endif
                @else
                    <p class="text-secondary mb-4">You don't have access to this page. Sign in with an account that does.</p>
                @endif

                <div class="d-flex flex-wrap justify-content-center gap-2">
                    @if ($home)
                        <a class="btn btn-success rounded-pill px-4" href="{{ route($home[0]) }}">{{ $home[1] }}</a>
                    @endif
                    @if ($logout)
                        {{-- The navbar's shared sign-out confirmation (partials/signout-dialog). --}}
                        <a class="btn btn-outline-danger rounded-pill px-4" href="#confirm-signout" data-open-dialog="confirm-signout"><i class="bi bi-box-arrow-right me-1" aria-hidden="true"></i>Sign out</a>
                    @endif
                    <a class="btn btn-link" href="{{ url('/') }}">Back to home</a>
                </div>

                <p class="small text-secondary mt-4 mb-0">Error 403</p>
            </div>
        </div>
    </div>
</section>
@endsection
