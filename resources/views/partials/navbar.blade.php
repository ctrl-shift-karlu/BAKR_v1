{{-- Site header. The current page is highlighted from <body data-page="..."> (see bakr-layout.css). --}}
<nav class="navbar navbar-expand-xl bk-navbar" aria-label="Main navigation">
    <div class="container">
        <a class="navbar-brand" href="{{ url('/') }}">
            BAKR
            <span class="brand-subtitle">{{ config('bakr.tagline') }}</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavigation" aria-controls="mainNavigation" aria-expanded="false" aria-label="Toggle navigation">
            <span class="bk-burger" aria-hidden="true"><i></i><i></i><i></i></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNavigation">
            <ul class="navbar-nav ms-auto align-items-xl-center">
                <li class="nav-item"><a class="nav-link" data-nav="home" href="{{ url('/') }}">Home</a></li>
                <li class="nav-item"><a class="nav-link" data-nav="scholarships" href="{{ url('scholarships') }}">Scholarships</a></li>
                <li class="nav-item"><a class="nav-link" data-nav="about" href="{{ url('about') }}">About Us</a></li>
                <li class="nav-item"><a class="nav-link" data-nav="faqs" href="{{ url('/') }}#faqs">FAQs</a></li>
                <li class="nav-item"><a class="nav-link" data-nav="contact" href="{{ url('contact') }}">Contact Us</a></li>
                <li class="nav-item nav-theme">@include('partials.theme-toggle', ['class' => 'is-nav', 'withLabel' => true])</li>
                {{-- Matching ("Find a Scholarship") is for students and visitors, not providers or scholar accounts. --}}
                @unless (in_array(auth()->user()?->role, ['provider', 'scholar', 'admin'], true))
                <li class="nav-item find-button"><a class="nav-link" href="{{ route('find') }}">Find a Scholarship</a></li>
                @endunless
                @php $me = auth()->user(); @endphp
                @if ($me && in_array($me->role, ['student', 'scholar', 'provider', 'admin'], true))
                    {{-- Signed in: one clear way back to the dashboard, plus sign out. No dropdown. --}}
                    @php
                        [$dashRoute, $first, $logoutRoute] = match ($me->role) {
                            'provider' => ['provider.dashboard', 'Provider dashboard', 'provider.logout'],
                            'scholar' => ['scholar.overview', 'Scholar portal', 'scholar.logout'],
                            'admin' => ['admin.overview', 'Admin portal', 'admin.logout'],
                            default => ['student.dashboard', 'My dashboard', 'student.logout'],
                        };
                        $initials = collect(preg_split('/\s+/', trim($me->name)))->filter()->take(2)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('');
                    @endphp
                    <li class="nav-item nav-account">
                        <a class="nav-link" href="{{ route($dashRoute) }}" title="Signed in as {{ $me->name }}">
                            <span class="nav-account-avatar" aria-hidden="true">{{ $initials ?: 'B' }}</span>
                            <span>{{ $first }}</span>
                        </a>
                    </li>
                    {{-- Same sign-out as every portal: the shared red confirmation (partials/signout-dialog, below). --}}
                    <li class="nav-item nav-signout">
                        <a class="nav-link" href="#confirm-signout" data-open-dialog="confirm-signout" aria-label="Sign out" title="Sign out"><i class="bi bi-box-arrow-right" aria-hidden="true"></i><span class="d-xl-none ms-2">Sign out</span></a>
                    </li>
                @else
                    {{-- Signed out: Sign in (by role). New students register via "Find a Scholarship" or the last menu item. --}}
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Sign in</a>
                        <ul class="dropdown-menu dropdown-menu-end bakr-login-menu">
                            <li><span class="dropdown-header">Sign in as</span></li>
                            @foreach ([
                                ['studentLogin', 'bi-person-check', 'Student', 'Find and track your matches'],
                                ['scholarLogin', 'bi-mortarboard', 'Scholar', 'Manage your scholarship'],
                                ['adminLogin', 'bi-shield-lock', 'Administrator', 'Analytics and accounts'],
                                ['providerLogin', 'bi-building', 'Scholarship Provider', 'Manage your scholarships and scholars'],
                            ] as [$path, $icon, $label, $hint])
                                <li>
                                    <a class="dropdown-item" href="{{ url($path) }}">
                                        <span class="bakr-dd-icon"><i class="bi {{ $icon }}" aria-hidden="true"></i></span>
                                        <span class="bakr-dd-text"><strong>{{ $label }}</strong><small>{{ $hint }}</small></span>
                                        <i class="bi bi-arrow-right bakr-dd-arrow" aria-hidden="true"></i>
                                    </a>
                                </li>
                            @endforeach
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="{{ route('student.register') }}">
                                    <span class="bakr-dd-icon"><i class="bi bi-person-plus" aria-hidden="true"></i></span>
                                    <span class="bakr-dd-text"><strong>New student?</strong><small>Create a free student account</small></span>
                                    <i class="bi bi-arrow-right bakr-dd-arrow" aria-hidden="true"></i>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif
            </ul>
        </div>
    </div>
</nav>
@if (isset($logoutRoute))
    @include('partials.signout-dialog', ['logoutRoute' => $logoutRoute])
@endif
