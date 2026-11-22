@extends('layouts.app')

@section('title', config('bakr.system_name'))
@section('description', 'BAKR is built by DNSC students, for you. See which scholarships fit you, what to prepare, and how to apply with the DNSC Scholarship and Grants Unit or the official provider.')
@section('page', 'home')
@php
    // Live counts from the scholarships table (they were hard-coded as 11 / 4-5-2 before 2026-11-18).
    $bySector = \App\Models\Scholarship::where('is_active', true)->pluck('sector')->countBy();
    $scholarshipCount = $bySector->sum();
    [$nInternal, $nGov, $nPrivate] = [(int) ($bySector['internal'] ?? 0), (int) ($bySector['government'] ?? 0), (int) ($bySector['private'] ?? 0)];
@endphp

@push('preloader')
    @include('partials.preloader')
@endpush

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/bakr-home.css') }}">
@endpush

@section('content')
@php($featured = \App\Models\Scholarship::whereIn('slug', ['dnsc-academic', 'dnsc-gia-a', 'dnsc-gia-b'])->get()->keyBy('slug'))
{{-- HERO: full-width photo, copy on the left, frosted-glass cards floating on the right. --}}
<section class="bk-hero" aria-labelledby="heroTitle">
    <div class="bk-hero-bg" aria-hidden="true">
        <img src="{{ asset('images/img/dnsc_campus.jpg') }}" alt="">
    </div>

    <div class="container bk-hero-grid">
        <div class="bk-hero-copy">
            <h1 id="heroTitle">Find opportunities that <em>fit your story.</em></h1>
            <p>BAKR is built by DNSC students, for you. Fill in one form and see which scholarships fit you, what you still need to prepare, and where to apply.</p>

            <div class="bk-hero-actions">
                <a href="{{ route('find') }}" class="nx-btn nx-btn-gold"><i class="bi bi-compass" aria-hidden="true"></i> Find My Matches</a>
                <a href="{{ url('scholarships') }}" class="nx-btn bk-btn-outline"><i class="bi bi-journal-bookmark-fill" aria-hidden="true"></i> Browse Scholarships</a>
            </div>

        </div>

        {{-- Right side: who offers the scholarships BAKR tracks. Logos only, kept simple. --}}
        <div class="bk-hero-providers">
            <span class="bk-hero-providers-label">Scholarships from</span>
            <ul aria-label="Scholarship providers">
                @foreach ([
                    ['dnss_logo.jpg', 'DNSC'],
                    ['CHED-Logo.png', 'CHED'],
                    ['DOST-Logo.png', 'DOST'],
                    ['GRAB-Logo.svg', 'Grab'],
                    ['CEBUANA-Logo.jpg', 'Cebuana'],
                    ['LANDBANK-Logo.svg', 'LANDBANK'],
                    ['BFAR-Logo.svg', 'BFAR'],
                ] as [$logo, $name])
                    <li><span class="bk-provider-logo"><img src="{{ asset('images/logo/'.$logo) }}" alt=""></span><span>{{ $name }}</span></li>
                @endforeach
            </ul>
        </div>
    </div>
</section>

{{-- Feature gallery, directly below the hero --}}
<section class="bakr-gallery-band" aria-label="Campus and student life gallery">
  <div class="bakr-gallery-shell">
    <div class="bakr-gallery-track">
      <figure class="bakr-gallery-card bakr-gallery-side bakr-gallery-left">
        <img src="{{ asset ('images/img/opt/student.jpg')}}" alt="Student learning and campus life" loading="lazy">
        <figcaption><span>Student life</span><strong>Learning with purpose</strong></figcaption>
      </figure>
      <figure class="bakr-gallery-card bakr-gallery-mid bakr-gallery-left-mid">
        <img src="{{ asset ('images/img/opt/dnsc_gate.jpg')}}" alt="Davao del Norte State College campus entrance" loading="lazy">
        <figcaption><span>Our community</span><strong>A campus to grow in</strong></figcaption>
      </figure>
      <figure class="bakr-gallery-card bakr-gallery-featured">
        <img src="{{ asset ('images/img/dnsc_campus.jpg')}}" alt="Davao del Norte State College campus" loading="lazy">
        <figcaption><span>Made for DNSC students</span><strong>Find your next opportunity</strong></figcaption>
      </figure>
      <figure class="bakr-gallery-card bakr-gallery-mid bakr-gallery-right-mid">
        <img src="{{ asset ('images/img/opt/students2.jpg')}}" alt="Students working together" loading="lazy">
        <figcaption><span>Student success</span><strong>Every journey matters</strong></figcaption>
      </figure>
      <figure class="bakr-gallery-card bakr-gallery-side bakr-gallery-right">
        <img src="{{ asset ('images/img/opt/lib.jpg')}}" alt="Student community and achievement" loading="lazy">
        <figcaption><span>Future focused</span><strong>Make your effort count</strong></figcaption>
      </figure>
    </div>
  </div>
</section>

{{-- Centred introduction --}}
<section class="bakr-intro-section" aria-labelledby="bakr-intro-title">
  <div class="container bakr-intro-inner">
    <span class="bakr-eyebrow">YOUR NEXT CHAPTER STARTS HERE</span>
    <h2 id="bakr-intro-title">Discover opportunities. <span>Shape your future.</span></h2>
    <p>BAKR puts the scholarships open to you in one place and tells you which ones fit. It is a student project: it does not take or send applications for you. When you find a match, read the provider's requirements and apply through the DNSC Scholarship and Grants Unit or the official provider.</p>
    <a href="{{ route('find') }}" class="nx-btn nx-btn-gold"><i class="bi bi-compass" aria-hidden="true"></i> Find Your Scholarship</a>
  </div>
</section>

{{-- AT A GLANCE: one panel, real numbers from the scholarship directory. --}}
<section class="bk-glance" aria-labelledby="glanceTitle">
    <div class="container">
        <div class="bk-ledger nx-reveal">
            <div class="bk-ledger-intro">
                <span class="bk-ledger-kicker">BAKR at a glance</span>
                <h2 id="glanceTitle">{{ $scholarshipCount }} scholarships. One place to start.</h2>
                <p>From DNSC grants to national programs and private foundations, each one with its requirements explained in plain words.</p>
                <a href="{{ url('scholarships') }}" class="bk-ledger-link">Open the directory <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
            </div>

            <dl class="bk-ledger-grid">
                <div class="bk-ledger-cell">
                    <dt>Programs listed</dt>
                    <dd><span class="bk-ledger-num" data-count="{{ $scholarshipCount }}">{{ $scholarshipCount }}</span>
                        <small>Each one shows you its benefits, requirements and official link.</small></dd>
                </div>
                <div class="bk-ledger-cell">
                    <dt>Sectors</dt>
                    <dd><span class="bk-ledger-num" data-count="3">3</span>
                        <span class="bk-split" role="img" aria-label="{{ $nInternal }} DNSC programs, {{ $nGov }} government programs, {{ $nPrivate }} private programs">
                            <i style="flex: {{ $nInternal }}" class="s1"></i><i style="flex: {{ $nGov }}" class="s2"></i><i style="flex: {{ $nPrivate }}" class="s3"></i>
                        </span>
                        <small><b>{{ $nInternal }}</b> DNSC &middot; <b>{{ $nGov }}</b> Government &middot; <b>{{ $nPrivate }}</b> Private</small></dd>
                </div>
                <div class="bk-ledger-cell">
                    <dt>Steps to apply</dt>
                    <dd><span class="bk-ledger-num" data-count="4">4</span>
                        <small>Create your account, fill in your form, review your matches, then apply with the provider.</small></dd>
                </div>
                <div class="bk-ledger-cell">
                    <dt>Office for submissions</dt>
                    <dd><span class="bk-ledger-num" data-count="1">1</span>
                        <small>You hand in your final application to the DNSC Scholarship and Grants Unit.</small></dd>
                </div>
            </dl>
        </div>
    </div>
</section>

{{-- ============================================================
     HOW IT WORKS
     ============================================================ --}}
<section class="nx-section nx-steps">
    <div class="container">
        <div class="row g-4 nx-reveal-stagger">
            <div class="col-md-4">
                <div class="nx-step">
                    <span class="nx-step-n" aria-hidden="true">1</span>
                    <div class="nx-icon-tile"><i class="bi bi-bookshelf" aria-hidden="true"></i></div>
                    <h3>Explore the Shelf</h3>
                    <p>Browse DNSC, government and private scholarships, sorted so you can quickly find the ones that matter to you.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="nx-step">
                    <span class="nx-step-n" aria-hidden="true">2</span>
                    <div class="nx-icon-tile"><i class="bi bi-ui-checks-grid" aria-hidden="true"></i></div>
                    <h3>Fill In Your Matching Form</h3>
                    <p>Sign up and fill in one form. BAKR checks it against every scholarship's rules, and an AI matcher reads it the way a scholarship officer would.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="nx-step">
                    <span class="nx-step-n" aria-hidden="true">3</span>
                    <div class="nx-icon-tile"><i class="bi bi-link-45deg" aria-hidden="true"></i></div>
                    <h3>Apply with the Provider</h3>
                    <p>Read what you need to prepare, then apply through the provider's official process.</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ============================================================
     SCHOLARSHIP SHOWCASE (single section: featured DNSC programs + link to full directory)
     The three modals below (#dnscAcademicModal, #giaCategoryAModal, #giaCategoryBModal) are opened from these cards.
     ============================================================ --}}
<section class="nx-section nx-scholarship-strip" id="scholarships" aria-labelledby="scholarships-title">
    <div class="container">
        <div class="nx-strip-heading">
            <h2 class="nx-h2" id="scholarships-title">Scholarships to start with</h2>
            <p class="nx-lead">Three of the {{ $scholarshipCount }} scholarships in BAKR. Open a card to see what it offers, who qualifies and which documents you need, then check the latest requirements with the DNSC Scholarship and Grants Unit.</p>
        </div>
        <div class="bakr-dnsc-scholarship-grid nx-reveal-stagger">
            <article class="bakr-dnsc-card">
                <div class="bakr-dnsc-card-image">
                    <img src="{{ asset ('images/img/opt/student.jpg')}}" alt="DNSC student engaged in academic learning" loading="lazy">
                    <span class="bakr-dnsc-image-tag"><i class="bi bi-building" aria-hidden="true"></i> DNSC Internal</span>
                    <span class="bakr-dnsc-seal"><img src="{{ asset ('images/logo/dnss_logo.jpg')}}" alt="DNSC seal" loading="lazy"></span>
                </div>
                <div class="bakr-dnsc-card-body">
                    <span class="bakr-dnsc-kicker">Academic support</span>
                    <h3 id="dnscAcademicTitle">DNSC Academic Scholarship</h3>
                    <p>{{ $featured['dnsc-academic']->overview ?? '' }}</p>
                    <button type="button" class="bakr-dnsc-details" data-bs-toggle="modal" data-bs-target="#dnscAcademicModal">View Details <i class="bi bi-arrow-up-right" aria-hidden="true"></i></button>
                </div>
            </article>
            <article class="bakr-dnsc-card">
                <div class="bakr-dnsc-card-image">
                    <img src="{{ asset ('images/img/catA.jpe')}}" alt="Students participating in campus activities" loading="lazy">
                    <span class="bakr-dnsc-image-tag"><i class="bi bi-building" aria-hidden="true"></i> DNSC Internal</span>
                    <span class="bakr-dnsc-seal"><img src="{{ asset ('images/logo/dnss_logo.jpg')}}" alt="DNSC seal" loading="lazy"></span>
                </div>
                <div class="bakr-dnsc-card-body">
                    <span class="bakr-dnsc-kicker">Talent and participation</span>
                    <h3>Grant-in-Aid (GIA) — Category A</h3>
                    <p>{{ $featured['dnsc-gia-a']->overview ?? '' }}</p>
                    <button type="button" class="bakr-dnsc-details" data-bs-toggle="modal" data-bs-target="#giaCategoryAModal">View Details <i class="bi bi-arrow-up-right" aria-hidden="true"></i></button>
                </div>
            </article>
            <article class="bakr-dnsc-card">
                <div class="bakr-dnsc-card-image">
                    <img src="{{ asset ('images/img/opt/catB.jpg')}}" alt="DNSC student representing campus leadership and participation" loading="lazy">
                    <span class="bakr-dnsc-image-tag"><i class="bi bi-building" aria-hidden="true"></i> DNSC Internal</span>
                    <span class="bakr-dnsc-seal"><img src="{{ asset ('images/logo/dnss_logo.jpg')}}" alt="DNSC seal" loading="lazy"></span>
                </div>
                <div class="bakr-dnsc-card-body">
                    <span class="bakr-dnsc-kicker">Student leadership</span>
                    <h3>Grant-in-Aid (GIA) — Category B</h3>
                    <p>{{ $featured['dnsc-gia-b']->overview ?? '' }}</p>
                    <button type="button" class="bakr-dnsc-details" data-bs-toggle="modal" data-bs-target="#giaCategoryBModal">View Details <i class="bi bi-arrow-up-right" aria-hidden="true"></i></button>
                </div>
            </article>
        </div>
        <div class="text-center mt-5">
            <a href="{{ route('scholarships') }}" class="nx-btn nx-btn-green">See all {{ $scholarshipCount }} scholarships <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
        </div>
    </div>
</section>

<section class="nx-section nx-photo-section" id="process">
    <div class="container">
        <div class="row g-5 align-items-center">
            <div class="col-lg-5"><h2 class="nx-h2">From first search to official application.</h2><p class="nx-lead">Four simple steps, and you stay in control of every one.</p><a href="{{ route('student.register') }}" class="nx-btn nx-btn-green">Create My Student Account</a></div>
            <div class="col-lg-7"><div class="nx-local-list nx-reveal-stagger"><div class="nx-local-item"><i class="bi bi-1-circle-fill"></i><div><strong>Create your account</strong><div>Sign up as a student. It takes less than a minute.</div></div></div><div class="nx-local-item"><i class="bi bi-2-circle-fill"></i><div><strong>Fill in your matching form</strong><div>Answer only what scholarships actually check. You can change your answers any time.</div></div></div><div class="nx-local-item"><i class="bi bi-3-circle-fill"></i><div><strong>Review your matches</strong><div>See where you qualify, what to prepare and what to confirm.</div></div></div><div class="nx-local-item"><i class="bi bi-4-circle-fill"></i><div><strong>Apply with the provider</strong><div>Send your final application through the official DNSC or provider process.</div></div></div></div></div>
        </div>
    </div>
</section>

{{-- ============================================================
     FAQS
     ============================================================ --}}
<section class="nx-section nx-faq" id="faqs">
    <div class="container">
        <div class="text-center mb-5 nx-reveal">
            <h2 class="nx-h2">Frequently asked questions</h2>
            <p class="nx-lead mx-auto">Quick answers about what BAKR does for you, and where you apply for real.</p>
        </div>
        <div class="nx-faq-list nx-reveal-stagger">
            <details class="nx-faq-item" open>
                <summary>What is BAKR and how does it help me?</summary>
                <div class="nx-faq-answer">BAKR is a student project made for Davao del Norte State College. It gathers scholarships in one place, shows you what each one asks for, and compares your matching form with them to point you to the ones that fit you.</div>
            </details>
            <details class="nx-faq-item">
                <summary>Can I submit my scholarship application inside BAKR?</summary>
                <div class="nx-faq-answer">No. BAKR does not receive or send applications. Use it to find your matches and get ready, then submit your final application to the DNSC Scholarship and Grants Unit or the official provider, depending on the scholarship.</div>
            </details>
            <details class="nx-faq-item">
                <summary>Can I hold more than one scholarship at the same time?</summary>
                <div class="nx-faq-answer">It depends on the scholarship. Some do not allow you to receive another grant at the same time, while others do. Tell BAKR what you already receive in your form so it can check this for you. Before you apply to a second scholarship, ask the DNSC Scholarship and Grants Unit or read the provider's guidelines, so a new award does not put your first one at risk.</div>
            </details>
            <details class="nx-faq-item">
                <summary>What documents will I usually need?</summary>
                <div class="nx-faq-answer">For the DNSC programs listed in BAKR, the common documents are your latest Certificate of Registration (COR), your latest grades or academic record, and your school ID. Some programs add proof specific to them, such as proof of talent or awards for Grant-in-Aid Category A, or proof of your Student Supreme Council or Student Publication position for Category B. The Scholarship and Grants Unit may ask for more, and government and private providers have their own lists, so always check the current requirements before you apply.</div>
            </details>
            <details class="nx-faq-item">
                <summary>How does BAKR choose matches?</summary>
                <div class="nx-faq-answer">BAKR does two checks. First, its rules compare your answers with each scholarship's published limits, such as year level, program, grades and family income. Then an AI matcher (Google Gemini) reads your whole matching form, without your name, e-mail or birth date, and judges how well you fit. You always see the stricter result. Your matches are a starting point to help you decide where to apply, not an official decision.</div>
            </details>
            <details class="nx-faq-item">
                <summary>Does a match mean I will receive the scholarship?</summary>
                <div class="nx-faq-answer">No. A match only means your answers seem to fit the scholarship's listed requirements. The DNSC Scholarship and Grants Unit or the provider decides who gets it, after reviewing your documents and counting the available slots.</div>
            </details>
            <details class="nx-faq-item">
                <summary>Where can I confirm scholarship status, requirements, and schedules?</summary>
                <div class="nx-faq-answer">Ask the DNSC Scholarship and Grants Unit about DNSC scholarships, or the official provider about government and private ones. Openings, deadlines and document lists change, so BAKR may not always show the latest update. If something is wrong on the BAKR website, write to us at <a href="mailto:bakr.dnsc@gmail.com">bakr.dnsc@gmail.com</a>.</div>
            </details>
            <details class="nx-faq-item">
                <summary>Where does my feedback go?</summary>
                <div class="nx-faq-answer">The message you send on the Contact page is saved and e-mailed to the BAKR team, and only we can read it. If you tick the box to share your feedback, it appears on the public page only after our staff approve it.</div>
            </details>
        </div>
    </div>
</section>
@endsection

@push('modals')
    @include('partials.modals.scholarship-details')
    @include('partials.modals.welcome')
@endpush

@push('scripts')
    <script src="{{ asset('js/bakr-home.js') }}"></script>
@endpush
