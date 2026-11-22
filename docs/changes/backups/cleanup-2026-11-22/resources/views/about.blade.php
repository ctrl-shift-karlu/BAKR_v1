@extends('layouts.app')

@section('title', 'About Us | BAKR')
@section('description', 'BAKR is a scholarship matching and scholar monitoring system, built by DNSC students for DNSC students.')
@section('page', 'about')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/bakr-pages.css') }}">
    <link rel="stylesheet" href="{{ asset('css/bakr-contact.css') }}">{{-- message-form styles, reused by the feedback section --}}
    <link rel="stylesheet" href="{{ asset('css/bakr-about.css') }}">
@endpush

@section('content')
<header class="bk-page-hero" style="--page-hero-image: url('{{ asset('images/img/dnsc_campus.jpg') }}')">
    <div class="container">
        <ol class="bk-crumbs" aria-label="Breadcrumb">
            <li><a href="{{ url('/') }}">Home</a></li>
            <li aria-current="page">About Us</li>
        </ol>
        <h1>About <em>BAKR</em></h1>
        <p>A scholarship guide built by DNSC students, to help you discover opportunities and know what to do next.</p>
    </div>
</header>

<!-- ============================================================
     ABOUT
     ============================================================ -->
<section id="about" class="nx-about">
    <div class="container">
        <div class="nx-about-panel">
            <div class="nx-about-grid">
                <div class="nx-about-img">
                    <div class="nx-about-frame">
                        <!-- VIDEO PLACEHOLDER
                             Put your file here:  public/video/dnsc-about-video.mp4
                             The poster image shows while the video loads (or if the file is missing). -->
                        @php
                            // Autoplay only a lightweight file. A heavy one (the current export is ~37 MB) waits for a tap,
                            // so students on mobile data are not charged for it. Re-encode to < 6 MB and autoplay returns.
                            $aboutVideo = public_path('video/dnsc-about-video.mp4');
                            $videoAutoplay = is_file($aboutVideo) && filesize($aboutVideo) < 6 * 1024 * 1024;
                        @endphp
                        <video class="nx-about-video" data-autoplay="{{ $videoAutoplay ? 'true' : 'false' }}"
                               muted loop playsinline
                               preload="none"
                               poster="{{ asset('images/img/dnsc_campus.jpg') }}"
                               aria-label="Video of the DNSC campus">
                            {{-- src is attached by bakr-about.js only when the video is on screen (and not on data saver / slow connections). --}}
                            <source data-src="{{ asset('video/dnsc-about-video.mp4') }}" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                        <span class="nx-about-caption">DNSC &middot; Campus Life</span>
                        <button type="button" class="nx-video-sound" id="aboutVideoSound" aria-pressed="false" aria-label="Turn video sound on">
                            <i class="bi bi-volume-mute-fill" aria-hidden="true"></i> <span>Sound off</span>
                        </button>
                    </div>
                    <p class="nx-about-credit">
                        Video credit:
                        <a href="https://dnsc.edu.ph/video-gallery/" target="_blank" rel="noopener noreferrer">DNSC Video Gallery</a>
                        &middot; Davao del Norte State College
                    </p>
                </div>

                <div class="nx-about-copy">
                    <h2 class="nx-h2">Connecting DNSC students with <em>opportunity.</em></h2>
                    <div class="nx-about-rule" aria-hidden="true"><span></span></div>
                    <p class="nx-about-text">
                        BAKR is a scholarship matching and scholar monitoring system,
                        built by DNSC students as a project for Davao del Norte State College.
                    </p>
                    <p class="nx-about-text">
                        BAKR helps you discover scholarships using what you tell it: your year level,
                        program, grades, citizenship, family income and anything else a scholarship asks for.
                    </p>

                    <div class="nx-points">
                        <div class="nx-point">
                            <b class="nx-point-no" aria-hidden="true">I</b>
                            <div><strong>Scholarship Matching</strong><span>We compare your information with each scholarship's requirements and show you where you fit.</span></div>
                        </div>
                        <div class="nx-point">
                            <b class="nx-point-no" aria-hidden="true">II</b>
                            <div><strong>External Application</strong><span>BAKR does not process applications. You submit your final application to the DNSC Scholarship and Grants Unit or the official provider.</span></div>
                        </div>
                        <div class="nx-point">
                            <b class="nx-point-no" aria-hidden="true">III</b>
                            <div><strong>Scholar Monitoring</strong><span>If you already hold a scholarship, you can follow your status and prepare your documents here.</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============================================================
     WHO WE ARE / PURPOSE / PROGRESS
     ============================================================ -->
<section class="nx-section nx-photo-section" id="who-we-are">
    <div class="container">
        <div class="nx-story-copy mb-5 nx-reveal">
            <h2 class="nx-h2">A local scholarship guide, made for you.</h2>
            <p class="nx-lead">BAKR brings scholarship information closer to you. We organize the opportunities, explain your next steps, and help you see how your own story fits a scholarship's requirements.</p>
        </div>
        <div class="nx-photo-grid nx-reveal-stagger">
            <div class="nx-photo-card tall">
                <img src="{{ asset ('images/img/lab.jpg')}}" alt="Student learning and campus life">
                <div class="nx-photo-caption"><strong>Progress you can see</strong><span>One clearer place to begin your scholarship journey.</span></div>
            </div>
            <div class="nx-photo-stack">
                <div class="nx-photo-card"><img loading="lazy" src="{{ asset ('images/img/opt/gate2.jpg')}}" alt="Davao del Norte State College campus entrance"><div class="nx-photo-caption"><strong>Built by student developers</strong><span>Designed around real student needs.</span></div></div>
                <div class="nx-photo-card"><img loading="lazy" src="{{ asset ('images/img/opt/students2.jpg')}}" alt="Students learning and collaborating"><div class="nx-photo-caption"><strong>Student-centered experience</strong><span>From finding a scholarship to staying organized once you have one.</span></div></div>
            </div>
        </div>
    </div>
</section>

<section class="nx-section" id="benefits">
    <div class="container">
        <div class="text-center mb-5 nx-reveal"><h2 class="nx-h2">What you can gain</h2><p class="nx-lead mx-auto">BAKR does not replace the official provider. It helps you prepare, compare and move forward with better information.</p></div>
        <div class="row g-4 nx-reveal-stagger">
            <div class="col-md-4"><div class="nx-benefit"><i class="bi bi-search-heart" aria-hidden="true"></i><h3>Discover relevant programs</h3><p>See the opportunities sorted by sector and read the requirements before you spend time on an application.</p></div></div>
            <div class="col-md-4"><div class="nx-benefit"><i class="bi bi-person-check" aria-hidden="true"></i><h3>Understand your fit</h3><p>We use your year level, program, grades, family income and achievements to show you where you fit.</p></div></div>
            <div class="col-md-4"><div class="nx-benefit"><i class="bi bi-folder-check" aria-hidden="true"></i><h3>Prepare with confidence</h3><p>Keep your required documents and the official way to apply in view while you take your next step.</p></div></div>
        </div>
    </div>
</section>

{{-- Sign-up call to action: after "What you can gain", so the team and the feedback form sit right above the footer. --}}
<section class="bk-cta-strip">
    <div class="container">
        <h2>Ready to find your match?</h2>
        <p>Create a student account, tell us a few details, and see which scholarships fit you.</p>
        <a href="{{ route('student.register') }}" class="nx-btn nx-btn-green"><i class="bi bi-person-plus" aria-hidden="true"></i> Create My Student Account</a>
    </div>
</section>

@include('partials.about.community-insights')
@include('partials.about.scholarship-office')
@include('partials.about.team')
@include('partials.about.feedback')
@endsection

@push('scripts')
    <script src="{{ asset('js/bakr-about.js') }}"></script>
@endpush
