<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#143a2b">
    <title>Create Student Account | BAKR</title>
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
                <h2>Find scholarships <em>made for you.</em></h2>
                <p>Create a free student account to start. Saving your information is not an application to any scholarship, and a match is not a guarantee of an award.</p>
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
                    <h1>Create your account</h1>
                    <p class="sa-help">It only takes a minute.</p>
                    @if ($errors->any())
                        <div class="alert alert-danger rounded-3 border-0 py-2 small" role="alert">Please check the highlighted fields.</div>
                    @endif
                    <form method="POST" action="{{ route('student.register') }}" novalidate>
                        @csrf
            <div class="mb-3">
                <label for="name" class="form-label">Full name</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                    <input id="name" name="name" type="text" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required autocomplete="name" autofocus>
                    
                </div>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required autocomplete="email">
                    
                </div>
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input id="password" name="password" type="password" class="form-control @error('password') is-invalid @enderror" required autocomplete="new-password" minlength="8">
                    <button class="btn sa-toggle" type="button" data-toggle-password="password" aria-label="Show password"><i class="bi bi-eye"></i></button>
                </div>
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Confirm password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                <input id="password_confirmation" name="password_confirmation" type="password" class="form-control" required autocomplete="new-password">
                            </div>
                            <div class="form-text">At least 8 characters, with letters and numbers.</div>
                        </div>

                        <button type="submit" class="sa-btn-gold w-100 mt-2"><i class="bi bi-person-plus me-1"></i> Create account</button>
                    </form>
                    <p class="text-center small sa-muted mt-3 mb-0">Already registered? <a href="{{ asset('studentLogin') }}">Sign in</a></p>
                </div>
            </div>

        </div>
    </div>
</main>
<script src="{{ asset('js/student-auth.js') }}"></script>
</body>
</html>
