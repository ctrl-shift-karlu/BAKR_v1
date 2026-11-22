{{-- Student registration: StudentAuthController (role and status are set server-side). --}}
@extends('layouts.auth')

@section('title', 'Create a Student Account')
@section('body', 'is-student')
@section('portal', 'Student Portal')

@section('intro_eyebrow', 'BAKR Student Portal')
@section('intro_title')Find scholarships <em>made for you.</em>@endsection
@section('intro_text', 'Create a free account, fill in one short form, and see which DNSC scholarships fit you.')
@section('intro_points')
    <li><i class="bi bi-ui-checks" aria-hidden="true"></i><span>Tell BAKR about your studies once and see the scholarships that fit you.</span></li>
    <li><i class="bi bi-shield-check" aria-hidden="true"></i><span>Your answers are encrypted, and you are the only one who can see them.</span></li>
    <li><i class="bi bi-arrow-repeat" aria-hidden="true"></i><span>Change your answers any time and your matches update.</span></li>
@endsection

@section('heading', 'Create your account')
@section('help', 'It only takes a minute.')

@section('card')
<form method="POST" action="{{ route('student.register') }}" novalidate data-busy="Creating your account…" data-busy-sub="Just a moment">
    @csrf
    <div class="mb-3">
        <label for="name" class="form-label">Full name</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-person" aria-hidden="true"></i></span>
            <input id="name" name="name" type="text" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required autocomplete="name" autofocus>
        </div>
        @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>
    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-envelope" aria-hidden="true"></i></span>
            <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required autocomplete="email">
        </div>
        @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>
    <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock" aria-hidden="true"></i></span>
            <input id="password" name="password" type="password" class="form-control @error('password') is-invalid @enderror" required autocomplete="new-password" minlength="8" aria-describedby="password-help">
            <button class="btn sa-toggle" type="button" data-toggle-password="password" aria-label="Show password"><i class="bi bi-eye" aria-hidden="true"></i></button>
        </div>
        @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>
    <div class="mb-3">
        <label for="password_confirmation" class="form-label">Confirm password</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock-fill" aria-hidden="true"></i></span>
            <input id="password_confirmation" name="password_confirmation" type="password" class="form-control" required autocomplete="new-password">
        </div>
        <div class="form-text" id="password-help">At least 8 characters, with letters and numbers.</div>
    </div>
    <button type="submit" class="sa-btn-gold w-100 mt-2"><i class="bi bi-person-plus me-1" aria-hidden="true"></i> Create account</button>
    <p class="form-text text-center mt-2 mb-0">By creating an account you agree to the <a href="{{ route('privacy') }}" target="_blank" rel="noopener">Privacy Policy</a>. You give your consent for matching at the end of the matching form.</p>
</form>
@endsection

@section('foot')
    <p>Already registered? <a href="{{ route('student.login') }}">Sign in</a></p>
@endsection
