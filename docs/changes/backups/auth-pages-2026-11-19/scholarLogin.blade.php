{{-- Scholar login: REAL authentication for scholar accounts (ScholarAuthController), which providers
     create when they add a scholar. Separate from the student (matching) login. Same design as the
     provider and student logins (student-auth.css; body.is-scholar sets the background photo). --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#143a2b">
    <title>Scholar Login | BAKR</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo/bakr-logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/student-auth.css') }}">
    <link rel="stylesheet" href="{{ asset('css/bakr-dark.css') }}">
    <script src="{{ asset('js/bakr-theme.js') }}"></script>
</head>
<body class="sa-body sa-auth is-scholar">
<main class="sa-auth-wrap">
    <div class="container">
        <div class="sa-auth-back text-end">@include('partials.theme-toggle', ['class' => 'is-auth'])<a href="{{ url('/') }}"><i class="bi bi-arrow-left me-1" aria-hidden="true"></i>Back to home</a></div>
        <div class="row align-items-center g-4 g-xl-5">

            <div class="col-lg-6 d-none d-lg-block sa-auth-intro">
                <div class="sa-eyebrow">BAKR Scholar Portal</div>
                <h2>Your scholarship, <em>all in one place.</em></h2>
                <p>Check your status, follow your stipend releases and keep your requirements complete each semester.</p>
                <ul class="sa-auth-points">
                    <li><i class="bi bi-patch-check" aria-hidden="true"></i><span>See your scholarship status and what you need to keep it.</span></li>
                    <li><i class="bi bi-cash-coin" aria-hidden="true"></i><span>Track each stipend release, from pending to received.</span></li>
                    <li><i class="bi bi-cloud-arrow-up" aria-hidden="true"></i><span>Upload the documents your provider asks for each semester.</span></li>
                </ul>
            </div>

            <div class="col-12 col-md-9 col-lg-5 offset-lg-1 mx-auto mx-lg-0">
                <div class="sa-auth-card">
                    <a href="{{ url('/') }}" class="sa-auth-brand">
                        <img src="{{ asset('images/logo/bakr-logo.png') }}" alt="">
                        <div>BAKR<span>Scholar Portal</span></div>
                    </a>
                    <h1>Scholar login</h1>
                    <p class="sa-help">Sign in with the e-mail your scholarship provider registered. Your first password was e-mailed to you.</p>
                    @if (session('status'))
                        <div class="alert alert-success rounded-3 border-0 py-2 small" role="status">{{ session('status') }}</div>
                    @endif
                    @if ($errors->any())
                        <div class="alert alert-danger rounded-3 border-0 py-2 small" role="alert">Please check the highlighted fields.</div>
                    @endif
                    <form method="POST" action="{{ route('scholar.login') }}" novalidate>
                        @csrf
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope" aria-hidden="true"></i></span>
                                <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required autocomplete="username" autofocus>
                            </div>
                            @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-baseline">
                                <label for="password" class="form-label">Password</label>
                                <a class="sa-forgot small" href="{{ route('student.password.request', ['from' => 'scholar']) }}">Forgot password?</a>
                            </div>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock" aria-hidden="true"></i></span>
                                <input id="password" name="password" type="password" class="form-control @error('password') is-invalid @enderror" required autocomplete="current-password">
                                <button class="btn sa-toggle" type="button" data-toggle-password="password" aria-label="Show password"><i class="bi bi-eye" aria-hidden="true"></i></button>
                            </div>
                            @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1">
                            <label class="form-check-label small" for="remember">Keep me signed in</label>
                        </div>

                        <button type="submit" class="sa-btn-gold w-100 mt-2"><i class="bi bi-box-arrow-in-right me-1" aria-hidden="true"></i> Sign in</button>
                    </form>
                    <p class="text-center small sa-muted mt-3 mb-0">Not a scholar yet? <a href="{{ route('student.login') }}">Student login</a> to find scholarships. No scholar account? <a href="{{ url('contact') }}">Contact the BAKR team</a></p>
                </div>
            </div>

        </div>
    </div>
</main>
<script src="{{ asset('js/student-auth.js') }}"></script>
</body>
</html>
