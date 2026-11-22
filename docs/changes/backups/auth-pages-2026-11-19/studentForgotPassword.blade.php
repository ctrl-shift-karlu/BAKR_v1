<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#143a2b">
    <title>Reset your password | BAKR</title>
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
        <div class="sa-auth-back text-end">@include('partials.theme-toggle', ['class' => 'is-auth'])<a href="{{ route($loginRoute) }}"><i class="bi bi-arrow-left me-1" aria-hidden="true"></i>Back to login</a></div>
        <div class="row justify-content-center">
            <div class="col-12 col-md-9 col-lg-5">
                <div class="sa-auth-card">
                    <a href="{{ url('/') }}" class="sa-auth-brand">
                        <img src="{{ asset('images/logo/bakr-logo.png') }}" alt="">
                        <div>BAKR<span>DNSC Scholarship Matching System</span></div>
                    </a>
                    <h1>Forgot your password?</h1>
                    <p class="sa-help">Enter the e-mail you sign in with (student, scholar or provider account). We will send a link to set a new password.</p>

                    @if (session('status'))
                        <div class="alert alert-success rounded-3 border-0 py-2 small" role="status">{{ session('status') }}</div>
                    @endif
                    @if ($errors->any())
                        <div class="alert alert-danger rounded-3 border-0 py-2 small" role="alert">Please check the highlighted field.</div>
                    @endif

                    <form method="POST" action="{{ route('student.password.email') }}" novalidate>
                        @csrf
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope" aria-hidden="true"></i></span>
                                <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required autocomplete="email" autofocus @error('email') aria-describedby="email-error" @enderror>
                            </div>
                            @error('email')<div class="invalid-feedback" id="email-error">{{ $message }}</div>@enderror
                        </div>
                        <button type="submit" class="sa-btn-gold w-100 mt-2"><i class="bi bi-send me-1" aria-hidden="true"></i> Send reset link</button>
                    </form>
                    <p class="text-center small sa-muted mt-3 mb-0">Remembered it? <a href="{{ route($loginRoute) }}">Back to login</a></p>
                </div>
            </div>
        </div>
    </div>
</main>
</body>
</html>
