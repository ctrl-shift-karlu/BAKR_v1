<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#143a2b">
    <meta name="referrer" content="no-referrer">
    <title>Change your password | BAKR</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo/bakr-logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/student-auth.css') }}">
    {{-- Only for the shared sign-out confirmation dialog --}}
    <link rel="stylesheet" href="{{ asset('css/bakr-dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/bakr-dark.css') }}">
    <script src="{{ asset('js/bakr-theme.js') }}"></script>
</head>
<body class="sa-body sa-auth is-forgot">
<main class="sa-auth-wrap">
    <div class="container">
        <div class="sa-auth-back text-end">
            @include('partials.theme-toggle', ['class' => 'is-auth'])
            <a class="sa-link-btn" href="#confirm-signout" data-open-dialog="confirm-signout"><i class="bi bi-box-arrow-right me-1" aria-hidden="true"></i>Sign out</a>
        </div>
        <div class="row justify-content-center">
            <div class="col-12 col-md-9 col-lg-5">
                <div class="sa-auth-card">
                    <a href="{{ url('/') }}" class="sa-auth-brand">
                        <img src="{{ asset('images/logo/bakr-logo.png') }}" alt="">
                        <div>BAKR<span>{{ $logoutRoute === 'scholar.logout' ? 'Scholar Portal' : 'DNSC Scholarship Matching System' }}</span></div>
                    </a>
                    <h1>Change your password</h1>
                    @if ($forced)
                        <div class="sa-notice mb-3" role="status">
                            <strong><i class="bi bi-shield-lock me-1" aria-hidden="true"></i>You signed in with a temporary password.</strong>
                            Choose your own password now. You can open your scholarship right after.
                        </div>
                    @else
                        <p class="sa-help">Use at least 8 characters, with letters and numbers.</p>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger rounded-3 border-0 py-2 small" role="alert">Please check the highlighted fields.</div>
                    @endif

                    <form method="POST" action="{{ route($updateRoute) }}" novalidate>
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="current_password" class="form-label">{{ $forced ? 'Temporary password (from the e-mail)' : 'Current password' }}</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-key" aria-hidden="true"></i></span>
                                <input id="current_password" name="current_password" type="password" class="form-control @error('current_password') is-invalid @enderror" required autocomplete="current-password" autofocus @error('current_password') aria-describedby="current-error" @enderror>
                                <button class="btn sa-toggle" type="button" data-toggle-password="current_password" aria-label="Show password"><i class="bi bi-eye" aria-hidden="true"></i></button>
                            </div>
                            @error('current_password')<div class="invalid-feedback" id="current-error">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">New password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock" aria-hidden="true"></i></span>
                                <input id="password" name="password" type="password" class="form-control @error('password') is-invalid @enderror" required autocomplete="new-password" minlength="8" aria-describedby="password-help @error('password') password-error @enderror">
                                <button class="btn sa-toggle" type="button" data-toggle-password="password" aria-label="Show password"><i class="bi bi-eye" aria-hidden="true"></i></button>
                            </div>
                            <div class="form-text" id="password-help">At least 8 characters, with letters and numbers.</div>
                            @error('password')<div class="invalid-feedback" id="password-error">{{ $message }}</div>@enderror
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
                </div>
            </div>
        </div>
    </div>
</main>
@include('partials.signout-dialog', ['logoutRoute' => $logoutRoute])
<script src="{{ asset('js/student-auth.js') }}"></script>
<script src="{{ asset('js/bakr-dashboard.js') }}"></script>
</body>
</html>
