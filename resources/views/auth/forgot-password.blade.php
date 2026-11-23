{{-- Forgot password (every account type): StudentPasswordResetController; $loginRoute = the login to go back to. --}}
@extends('layouts.auth')

@php
    $portal = ['scholar.login' => 'Scholar Portal', 'provider.login' => 'Provider Portal', 'admin.login' => 'Admin Portal'][$loginRoute] ?? 'Student Portal';
@endphp

@section('title', 'Forgot Password')
@section('body', 'is-forgot')
@section('portal', $portal)
@section('back')<a href="{{ route($loginRoute) }}"><i class="bi bi-arrow-left me-1" aria-hidden="true"></i>Back to login</a>@endsection

@section('heading', 'Forgot your password?')
@section('help', 'Enter the e-mail you sign in with and we will send you a link to set a new password.')
@section('error_summary', 'Please check the highlighted field.')

@section('card')
<form method="POST" action="{{ route('student.password.email') }}" novalidate data-busy="Sending your reset link…" data-busy-sub="We are e-mailing it to you">
    @csrf
    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-envelope" aria-hidden="true"></i></span>
            <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required autocomplete="email" autofocus @error('email') aria-describedby="email-error" @enderror>
        </div>
        @error('email')<div class="invalid-feedback d-block" id="email-error">{{ $message }}</div>@enderror
    </div>
    <button type="submit" class="sa-btn-gold w-100 mt-2"><i class="bi bi-send me-1" aria-hidden="true"></i> Send reset link</button>
</form>
@endsection

@section('foot')
    <p>Remembered it? <a href="{{ route($loginRoute) }}">Back to login</a></p>
    @if ($loginRoute === 'scholar.login')
        <p>No longer have access to that e-mail? Call or text your scholarship provider: they can change your sign-in e-mail once you confirm the new one.</p>
    @endif
@endsection
