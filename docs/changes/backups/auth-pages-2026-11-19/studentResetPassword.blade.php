<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#143a2b">
    <meta name="referrer" content="no-referrer">
    <title>Set a new password | BAKR</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/student-auth.css') }}">
    <link rel="stylesheet" href="{{ asset('css/bakr-dark.css') }}">
    <script src="{{ asset('js/bakr-theme.js') }}"></script>
</head>
<body class="sa-body sa-auth is-forgot">
<main class="sa-auth-wrap">
    <div class="container">
        <div class="sa-auth-back text-end">@include('partials.theme-toggle', ['class' => 'is-auth'])<a href="{{ route('student.login') }}"><i class="bi bi-arrow-left me-1" aria-hidden="true"></i>Back to login</a></div>
        <div class="row justify-content-center">
            <div class="col-12 col-md-9 col-lg-5">
                <div class="sa-auth-card">
                    <a href="{{ url('/') }}" class="sa-auth-brand">
                        <img src="{{ asset('images/logo/bakr-logo.png') }}" alt="">
                        <div>BAKR<span>DNSC Scholarship Matching System</span></div>
                    </a>
                    <h1>Set a new password</h1>
                    <p class="sa-help">Use at least 8 characters, with letters and numbers.</p>

                    @if ($errors->any())
                        <div class="alert alert-danger rounded-3 border-0 py-2 small" role="alert">
                            {{ $errors->first('email') ?: 'Please check the highlighted fields.' }}
                            @if ($errors->has('email'))<a href="{{ route('student.password.request') }}">Request a new link</a>@endif
                        </div>
                    @endif

                    <form method="POST" action="{{ route('student.password.update') }}" novalidate>
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
<script src="{{ asset('js/student-auth.js') }}"></script>
</body>
</html>
