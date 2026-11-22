{{-- Set a new password from the e-mailed link (any account type): StudentPasswordResetController. --}}
@extends('layouts.auth')

@section('title', 'Set a New Password')
@section('body', 'is-forgot')

@section('heading', 'Set a new password')
@section('help', 'Use at least 8 characters, with letters and numbers.')
@section('error_summary'){{ $errors->first('email') ?: 'Please check the highlighted fields.' }} @if ($errors->has('email'))<a href="{{ route('student.password.request') }}">Request a new link</a>@endif @endsection

@section('card')
<form method="POST" action="{{ route('student.password.update') }}" novalidate data-busy="Saving your new password…" data-busy-sub="Then we take you to sign in">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">
    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-envelope" aria-hidden="true"></i></span>
            <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $email) }}" required autocomplete="email">
        </div>
    </div>
    <div class="mb-3">
        <label for="password" class="form-label">New password</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock" aria-hidden="true"></i></span>
            <input id="password" name="password" type="password" class="form-control @error('password') is-invalid @enderror" required autocomplete="new-password" minlength="8" autofocus @error('password') aria-describedby="password-error" @enderror>
            <button class="btn sa-toggle" type="button" data-toggle-password="password" aria-label="Show password"><i class="bi bi-eye" aria-hidden="true"></i></button>
        </div>
        @error('password')<div class="invalid-feedback d-block" id="password-error">{{ $message }}</div>@enderror
    </div>
    <div class="mb-3">
        <label for="password_confirmation" class="form-label">Confirm new password</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock-fill" aria-hidden="true"></i></span>
            <input id="password_confirmation" name="password_confirmation" type="password" class="form-control" required autocomplete="new-password">
        </div>
    </div>
    <button type="submit" class="sa-btn-gold w-100 mt-2"><i class="bi bi-check2-circle me-1" aria-hidden="true"></i> Save new password</button>
</form>
@endsection
