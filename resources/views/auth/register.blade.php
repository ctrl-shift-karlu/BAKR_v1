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
    {{-- Name in parts (owner, 2026-11-23): first name + surname required, middle name + suffix optional. --}}
    <div class="row g-2 mb-1">
        <div class="col-sm-6 mb-2">
            <label for="first_name" class="form-label">First name</label>
            <input id="first_name" name="first_name" type="text" class="form-control @error('first_name') is-invalid @enderror" value="{{ old('first_name') }}" maxlength="60" required autocomplete="given-name" autofocus>
            @error('first_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>
        <div class="col-sm-6 mb-2">
            <label for="middle_name" class="form-label">Middle name <span class="sa-optional">(optional)</span></label>
            <input id="middle_name" name="middle_name" type="text" class="form-control @error('middle_name') is-invalid @enderror" value="{{ old('middle_name') }}" maxlength="60" autocomplete="additional-name">
            @error('middle_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>
        <div class="col-8 mb-2">
            <label for="last_name" class="form-label">Surname</label>
            <input id="last_name" name="last_name" type="text" class="form-control @error('last_name') is-invalid @enderror" value="{{ old('last_name') }}" maxlength="60" required autocomplete="family-name">
            @error('last_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>
        <div class="col-4 mb-2">
            <label for="suffix" class="form-label">Suffix <span class="sa-optional">(optional)</span></label>
            <select id="suffix" name="suffix" class="form-select @error('suffix') is-invalid @enderror" autocomplete="honorific-suffix">
                <option value="">None</option>
                @foreach (\App\Support\PersonName::SUFFIXES as $key => $label)
                    <option value="{{ $key }}" @selected(old('suffix') === $key)>{{ $label }}</option>
                @endforeach
            </select>
            @error('suffix')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>
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
