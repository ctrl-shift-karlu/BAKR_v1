@extends('scholar.layout')
@section('title', 'Settings | BAKR')

{{-- Scholar portal › Settings, modelled on the scholar prototype's Settings view (scholarDashboardF):
     "Edit password" (optional; the form opens with the Change password button — a <details>, so it
     works without JavaScript and stays open after a validation error), "Forgot your password?"
     (e-mails a reset link to the account's address) and a red "Sign out" card that opens the shared
     confirmation dialog (partials/signout-dialog, included by scholar.layout). --}}

@section('hero_title')<em>Settings</em>@endsection
@section('hero_sub', 'Your password, and signing out of this device.')

@section('portal')
<div class="dash-stack">
    {{-- ============ EDIT PASSWORD ============ --}}
    <section class="dash-card" id="password" aria-labelledby="pw-title">
        <details class="sc-collapse" @if ($errors->hasAny(['current_password', 'password'])) open @endif>
            <summary>
                <span>
                    <span class="dash-card-title" id="pw-title"><i class="bi bi-key" aria-hidden="true"></i>Edit password</span>
                    <span class="sc-card-sub">Optional. Change your password only if you want to.</span>
                </span>
                <span class="dash-btn is-soft is-sm sc-collapse-btn"><span class="sc-when-closed">Change password</span><span class="sc-when-open">Cancel</span></span>
            </summary>
            <form method="POST" action="{{ route('scholar.password.change.update') }}" class="dash-stack sc-collapse-body" novalidate data-busy="Saving your new password…" data-busy-sub="Just a moment">
                @csrf
                @method('PUT')
                <div class="dash-field">
                    <label for="current_password">Current password</label>
                    <input id="current_password" name="current_password" type="password" required autocomplete="current-password">
                    @error('current_password')<p class="dash-error">{{ $message }}</p>@enderror
                </div>
                <div class="dash-field">
                    <label for="password">New password</label>
                    <input id="password" name="password" type="password" required minlength="8" autocomplete="new-password" aria-describedby="pw-help">
                    <small id="pw-help">At least 8 characters, with letters and numbers.</small>
                    @error('password')<p class="dash-error">{{ $message }}</p>@enderror
                </div>
                <div class="dash-field">
                    <label for="password_confirmation">Confirm new password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password">
                </div>
                <div><button type="submit" class="dash-btn"><i class="bi bi-check2-circle" aria-hidden="true"></i>Update password</button></div>
            </form>
        </details>
    </section>

    {{-- ============ FORGOT PASSWORD ============ --}}
    <section class="dash-card" aria-labelledby="forgot-title">
        <h2 class="dash-card-title" id="forgot-title"><i class="bi bi-envelope-arrow-up" aria-hidden="true"></i>Forgot your password?</h2>
        <p class="sc-hint">Don't remember your current password? We'll e-mail a reset link to <b>{{ $user->email }}</b>. Open it within 60 minutes to choose a new password, then sign in again.</p>
        <form method="POST" action="{{ route('scholar.settings.reset-link') }}" class="pv-gap" data-busy="Sending your reset link…" data-busy-sub="We are e-mailing it to you">
            @csrf
            <button type="submit" class="dash-btn is-soft"><i class="bi bi-send" aria-hidden="true"></i>E-mail me a reset link</button>
        </form>
    </section>

    {{-- ============ SIGN OUT ============ --}}
    <section class="dash-card sc-signout" aria-labelledby="out-title">
        <div>
            <h2 class="dash-card-title" id="out-title"><i class="bi bi-box-arrow-right" aria-hidden="true"></i>Sign out</h2>
            <p class="sc-card-sub">End your current session on this device, especially on a shared or school computer.</p>
        </div>
        {{-- Without JavaScript the link opens the confirmation as a normal form (dialog :target fallback). --}}
        <a class="dash-btn is-danger" href="#confirm-signout" data-open-dialog="confirm-signout"><i class="bi bi-box-arrow-right" aria-hidden="true"></i>Sign out</a>
    </section>
</div>
@endsection
