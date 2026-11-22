{{-- Every sign-in page (2026-11-19: one layout instead of eight copies): the four logins, register,
     forgot / reset / change password. Styles: public/css/student-auth.css (background photo per
     portal via body.is-<portal>); dark mode: bakr-dark.css.
       title       <title>
       body        portal class: is-student | is-scholar | is-provider | is-admin | is-forgot
       portal      card subtitle under "BAKR" (the portal's name)
       back        top-right link (default: Back to home)
       intro_*     optional left column: eyebrow, title (may contain <em>), text, points (<li>s)
       heading, help, card (form), foot (links under the form)
     Without an intro the card is centred on its own. --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#143a2b">
    <meta name="referrer" content="no-referrer">
    <title>@yield('title') | BAKR</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo/bakr-logo.png') }}">
    @include('partials.pwa-head')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Source+Serif+4:ital,opsz,wght@0,8..60,600;1,8..60,600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/student-auth.css') }}">
    @stack('styles')
    <link rel="stylesheet" href="{{ asset('css/bakr-busy.css') }}">
    <link rel="stylesheet" href="{{ asset('css/bakr-dark.css') }}">
    <script src="{{ asset('js/bakr-theme.js') }}"></script>
</head>
<body class="sa-body sa-auth @yield('body', 'is-student')">
<main class="sa-auth-wrap">
    <div class="container">
        <div class="sa-auth-back text-end">
            @include('partials.theme-toggle', ['class' => 'is-auth'])
            @hasSection('back')@yield('back')@else<a href="{{ url('/') }}"><i class="bi bi-arrow-left me-1" aria-hidden="true"></i>Back to home</a>@endif
        </div>
        <div class="row align-items-center g-4 g-xl-5 {{ View::hasSection('intro_title') ? '' : 'justify-content-center' }}">

            @hasSection('intro_title')
            <div class="col-lg-6 d-none d-lg-block sa-auth-intro">
                <div class="sa-eyebrow">@yield('intro_eyebrow')</div>
                <h2>@yield('intro_title')</h2>
                <p>@yield('intro_text')</p>
                <ul class="sa-auth-points">@yield('intro_points')</ul>
            </div>
            @endif

            <div class="col-12 col-md-9 col-lg-5 {{ View::hasSection('intro_title') ? 'offset-lg-1 mx-auto mx-lg-0' : '' }}">
                <div class="sa-auth-card">
                    <a href="{{ url('/') }}" class="sa-auth-brand">
                        <img src="{{ asset('images/logo/bakr-logo.png') }}" alt="">
                        <div>BAKR<span>@yield('portal', config('bakr.tagline'))</span></div>
                    </a>
                    <h1>@yield('heading')</h1>
                    @hasSection('help')<p class="sa-help">@yield('help')</p>@endif

                    @if (session('status'))
                        <div class="alert alert-success rounded-3 border-0 py-2 small" role="status">{{ session('status') }}</div>
                    @endif
                    @if ($errors->any())
                        <div class="alert alert-danger rounded-3 border-0 py-2 small" role="alert">@yield('error_summary', 'Please check the highlighted fields.')</div>
                    @endif

                    @yield('card')

                    @hasSection('foot')<div class="sa-auth-foot">@yield('foot')</div>@endif
                    {{-- Every sign-in page (all four portals) links the Privacy Policy (2026-11-21). --}}
                    <p class="sa-auth-privacy"><i class="bi bi-shield-lock" aria-hidden="true"></i> <a href="{{ route('privacy') }}">Privacy Policy</a></p>
                </div>
            </div>

        </div>
    </div>
</main>
@stack('dialogs')
<script src="{{ asset('js/student-auth.js') }}"></script>
<script src="{{ asset('js/bakr-busy.js') }}" defer></script>
@stack('scripts')
</body>
</html>
