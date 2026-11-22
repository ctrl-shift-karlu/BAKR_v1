{{-- Change password (student or scholar; a scholar's first sign-in with the e-mailed temporary password
     lands here until it is changed): StudentPasswordChangeController. Sign out uses the shared dialog. --}}
@extends('layouts.auth')

@section('title', 'Change Your Password')
@section('body', 'is-forgot')
@section('portal', $logoutRoute === 'scholar.logout' ? 'Scholar Portal' : 'Student Portal')
@section('back')<a href="#confirm-signout" data-open-dialog="confirm-signout"><i class="bi bi-box-arrow-right me-1" aria-hidden="true"></i>Sign out</a>@endsection

@push('styles')
    {{-- Only for the shared sign-out confirmation dialog --}}
    <link rel="stylesheet" href="{{ asset('css/bakr-dashboard.css') }}">
@endpush

@section('heading', 'Change your password')
@unless ($forced)
    @section('help', 'Use at least 8 characters, with letters and numbers.')
@endunless

@section('card')
@if ($forced)
    <div class="sa-notice mb-3" role="status">
        <strong><i class="bi bi-shield-lock me-1" aria-hidden="true"></i>You signed in with a temporary password.</strong>
        Choose your own password now. You can open your scholarship right after.
    </div>
@endif
{{-- data-retain (public/js/student-auth.js): the temporary password is kept in this tab while the form is
     sent, so if the new password is refused the person does not have to dig the e-mail out again. --}}
<form method="POST" action="{{ route($updateRoute) }}" novalidate data-busy="Saving your new password…" data-busy-sub="Just a moment">
    @csrf
    @method('PUT')
    <div class="mb-3">
        <label for="current_password" class="form-label">{{ $forced ? 'Temporary password (from the e-mail)' : 'Current password' }}</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-key" aria-hidden="true"></i></span>
            <input id="current_password" name="current_password" type="password" class="form-control @error('current_password') is-invalid @enderror" required autocomplete="current-password" @if ($forced) data-retain="temp-password" @else autofocus @endif @error('current_password') aria-describedby="current-error" @enderror>
            <button class="btn sa-toggle" type="button" data-toggle-password="current_password" aria-label="Show password"><i class="bi bi-eye" aria-hidden="true"></i></button>
        </div>
        @error('current_password')<div class="invalid-feedback d-block" id="current-error">{{ $message }}</div>@enderror
    </div>
    <div class="mb-3">
        <label for="password" class="form-label">New password</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock" aria-hidden="true"></i></span>
            <input id="password" name="password" type="password" class="form-control @error('password') is-invalid @enderror" required autocomplete="new-password" minlength="8" aria-describedby="password-help @error('password') password-error @enderror">
            <button class="btn sa-toggle" type="button" data-toggle-password="password" aria-label="Show password"><i class="bi bi-eye" aria-hidden="true"></i></button>
        </div>
        <div class="form-text" id="password-help">At least 8 characters, with letters and numbers.</div>
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

@push('dialogs')
    @include('partials.signout-dialog', ['logoutRoute' => $logoutRoute])
@endpush
@push('scripts')
    <script src="{{ asset('js/bakr-dashboard.js') }}"></script>
@endpush
