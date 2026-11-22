<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#143a2b">
    <title>Student Login | BAKR</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/student-auth.css') }}">
    <link rel="stylesheet" href="{{ asset('css/bakr-dark.css') }}">
    <script src="{{ asset('js/bakr-theme.js') }}"></script>
</head>
<body class="sa-body sa-auth" style="--auth-bg: url('{{ asset('images/img/dnsc_campus.jpg') }}')">
<main class="sa-auth-wrap">
    <div class="container">
        <div class="sa-auth-back text-end">@include('partials.theme-toggle', ['class' => 'is-auth'])<a href="{{ url('/') }}"><i class="bi bi-arrow-left me-1"></i>Back to home</a></div>
        <div class="row align-items-center g-4 g-xl-5">

            <div class="col-lg-6 d-none d-lg-block sa-auth-intro">
                <div class="sa-eyebrow">DNSC Scholarship Matching</div>
                <h2>Welcome back. Your <em>matches</em> are waiting.</h2>
                <p>Sign in to review your scholarship matches and keep your information up to date.</p>
                <ul class="sa-auth-points">
                    <li><i class="bi bi-ui-checks" aria-hidden="true"></i><span>Tell BAKR about your studies once, and see scholarships that may fit you.</span></li>
                    <li><i class="bi bi-shield-check"></i><span>Your information stays private and is used only for matching.</span></li>
                    <li><i class="bi bi-arrow-repeat"></i><span>Update your details any time and refresh your matches.</span></li>
                </ul>
            </div>

            <div class="col-12 col-md-9 col-lg-5 offset-lg-1 mx-auto mx-lg-0">
                <div class="sa-auth-card">
                    <a href="{{ url('/') }}" class="sa-auth-brand">
                        <img src="{{ asset('images/logo/bakr-logo.png') }}" alt="" onerror="this.style.display='none'">
                        <div>BAKR<span>DNSC Scholarship Matching System</span></div>
                    </a>
                    <h1>Student login</h1>
                    <p class="sa-help">Sign in to your BAKR student account.</p>
                    @if (session('status'))
                        <div class="alert alert-success rounded-3 border-0 py-2 small" role="status">{{ session('status') }}</div>
                    @endif
                    @if ($errors->any())
                        <div class="alert alert-danger rounded-3 border-0 py-2 small" role="alert">Please check the highlighted fields.</div>
                    @endif
                    <form method="POST" action="{{ route('student.login') }}" novalidate>
                        @csrf
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required autocomplete="email" autofocus>
                    
                </div>
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-baseline">
                    <label for="password" class="form-label">Password</label>
                    <a class="sa-forgot small" href="{{ route('student.password.request') }}">Forgot password?</a>
                </div>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input id="password" name="password" type="password" class="form-control @error('password') is-invalid @enderror" required autocomplete="current-password">
                    <button class="btn sa-toggle" type="button" data-toggle-password="password" aria-label="Show password"><i class="bi bi-eye"></i></button>
                </div>
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1">
                            <label class="form-check-label small" for="remember">Keep me signed in</label>
                        </div>

                        <button type="submit" class="sa-btn-gold w-100 mt-2"><i class="bi bi-box-arrow-in-right me-1"></i> Sign in</button>
                    </form>
                    <p class="text-center small sa-muted mt-3 mb-0">New to BAKR? <a href="{{ asset('studentRegister') }}">Create a student account</a></p>
                    <p class="text-center small sa-muted mt-2 mb-0">Already a scholar? Use the <a href="{{ route('scholar.login') }}">Scholar login</a>.</p>
                </div>
            </div>

        </div>
    </div>
</main>
<script src="{{ asset('js/student-auth.js') }}"></script>
</body>
</html>
