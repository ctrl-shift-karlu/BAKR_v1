{{-- Outcome of the "confirm your new e-mail" link (ScholarEmailConfirmController, 2026-11-23).
     $state: changed | expired | invalid | taken. --}}
@extends('layouts.auth')

@section('title', $state === 'changed' ? 'E-mail confirmed' : 'Link not valid')
@section('body', 'is-scholar')
@section('portal', 'Scholar Portal')

@section('heading', match ($state) {
    'changed' => 'Your new e-mail is confirmed',
    'expired' => 'This link has expired',
    'taken' => 'This e-mail can no longer be used',
    default => 'This link does not work',
})

@section('card')
    @if ($state === 'changed')
        <div class="alert alert-success rounded-3 border-0 py-2 small" role="status">
            From now on, sign in at the Scholar login with <strong>{{ $email }}</strong>.
            {{ $loginSent ? 'We e-mailed new login details (a temporary password) to this address.' : 'Your password stays the same.' }}
        </div>
        <a class="sa-btn-gold w-100 mt-2 d-inline-block text-center text-decoration-none" href="{{ route('scholar.login') }}"><i class="bi bi-box-arrow-in-right me-1" aria-hidden="true"></i> Go to the Scholar login</a>
    @else
        <p class="sa-help">
            @switch ($state)
                @case('expired') The link works for {{ $hours }} hours. @break
                @case('taken') Another BAKR account started using this e-mail in the meantime. @break
                @default It was already used, withdrawn, or copied incompletely.
            @endswitch
            Nothing was changed: you still sign in with your current e-mail. Ask {{ $provider ?: 'your scholarship provider' }} to send a new link.
        </p>
        <a class="sa-btn-gold w-100 mt-2 d-inline-block text-center text-decoration-none" href="{{ route('scholar.login') }}"><i class="bi bi-box-arrow-in-right me-1" aria-hidden="true"></i> Go to the Scholar login</a>
    @endif
@endsection
