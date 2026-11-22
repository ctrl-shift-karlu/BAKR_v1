{{-- The e-mail + password sign-in form shared by the four logins (layouts.auth).
     $action  login route name     $from  ?from= for "Forgot password?" (student|scholar|provider|admin)
     $remember  show "Keep me signed in" (personal accounts only: students and scholars;
                staff accounts on shared office computers are not remembered). --}}
<form method="POST" action="{{ route($action) }}" novalidate data-busy="Signing you in…" data-busy-sub="Checking your details">
    @csrf
    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-envelope" aria-hidden="true"></i></span>
            <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required autocomplete="username" autofocus @error('email') aria-describedby="email-error" @enderror>
        </div>
        @error('email')<div class="invalid-feedback d-block" id="email-error">{{ $message }}</div>@enderror
    </div>
    <div class="mb-3">
        <div class="d-flex justify-content-between align-items-baseline">
            <label for="password" class="form-label">Password</label>
            <a class="sa-forgot small" href="{{ route('student.password.request', ['from' => $from]) }}">Forgot password?</a>
        </div>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock" aria-hidden="true"></i></span>
            <input id="password" name="password" type="password" class="form-control @error('password') is-invalid @enderror" required autocomplete="current-password" @error('password') aria-describedby="password-error" @enderror>
            <button class="btn sa-toggle" type="button" data-toggle-password="password" aria-label="Show password"><i class="bi bi-eye" aria-hidden="true"></i></button>
        </div>
        @error('password')<div class="invalid-feedback d-block" id="password-error">{{ $message }}</div>@enderror
    </div>
    @if ($remember ?? false)
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1" @checked(old('remember'))>
            <label class="form-check-label small" for="remember">Keep me signed in</label>
        </div>
    @endif
    <button type="submit" class="sa-btn-gold w-100 mt-2"><i class="bi bi-box-arrow-in-right me-1" aria-hidden="true"></i> Sign in</button>
</form>
