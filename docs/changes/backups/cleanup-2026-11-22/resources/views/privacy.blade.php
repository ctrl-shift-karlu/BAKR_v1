@extends('layouts.app')

{{-- Privacy Policy (owner, 2026-11-21), under the Data Privacy Act of 2012 (Republic Act No. 10173), its Implementing
     Rules and Regulations and the issuances of the National Privacy Commission. It describes what BAKR really does,
     portal by portal; keep it in step with the code (ApplicationSchema, MatchProfile, ProviderScope, the admin
     portal) and change config('bakr.privacy_updated') whenever the text changes. Students agree to it at the end of
     the matching form. --}}

@section('title', 'Privacy Policy | BAKR')
@section('description', 'How BAKR collects, uses, shares and protects your personal information under the Data Privacy Act of 2012 (RA 10173).')
@section('page', 'privacy')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/bakr-pages.css') }}">
    <link rel="stylesheet" href="{{ asset('css/bakr-privacy.css') }}">
@endpush

@php
    $updated = \Illuminate\Support\Carbon::parse(config('bakr.privacy_updated'))->format('F j, Y');
    $email = config('bakr.contact_to');
    $sections = [
        'who' => 'Who we are',
        'collect' => 'What we collect',
        'use' => 'Why we use it',
        'ai' => 'The AI matcher',
        'share' => 'Who can see your information',
        'basis' => 'Our lawful basis',
        'keep' => 'How we protect and keep it',
        'cookies' => 'Cookies and your browser',
        'rights' => 'Your rights',
        'minors' => 'Students under 18',
        'changes' => 'Changes and contact',
    ];
@endphp

@section('content')
<header class="bk-page-hero" style="--page-hero-image: url('{{ asset('images/img/opt/dnsc_gate.jpg') }}')">
    <div class="container">
        <ol class="bk-crumbs" aria-label="Breadcrumb">
            <li><a href="{{ url('/') }}">Home</a></li>
            <li aria-current="page">Privacy Policy</li>
        </ol>
        <h1>Privacy <em>Policy</em></h1>
        <p>How {{ config('bakr.system_name') }} collects, uses, shares and protects your personal information, under the Data Privacy Act of 2012 (Republic Act No. 10173).</p>
    </div>
</header>

<div class="container bk-page-body">
    <article class="pp-card">
        <p class="pp-updated"><i class="bi bi-clock-history" aria-hidden="true"></i> Last updated {{ $updated }}</p>

        <nav class="pp-toc" aria-label="On this page">
            <strong>On this page</strong>
            <ol>
                @foreach ($sections as $id => $title)
                    <li><a href="#{{ $id }}">{{ $title }}</a></li>
                @endforeach
            </ol>
        </nav>

        <section id="who" aria-labelledby="h-who">
            <h2 id="h-who">1. Who we are</h2>
            <p>BAKR is a scholarship matching and monitoring system for students of Davao del Norte State College (DNSC). It is run by the BAKR team, who act as the personal information controller for the data in BAKR. Scholarship providers (for example CHED, DOST, LANDBANK, BFAR, Cebuana Lhuillier Foundation, GrabScholar and the DNSC Scholarship and Grants Unit) use BAKR to monitor their own scholars.</p>
            <p>BAKR is not the official application system of any scholarship. You still apply to each provider through its own process, and the provider handles your application under its own privacy policy.</p>
        </section>

        <section id="collect" aria-labelledby="h-collect">
            <h2 id="h-collect">2. What we collect</h2>
            <p>We collect only what a feature needs. What we hold depends on how you use BAKR:</p>
            <dl class="pp-list">
                <div>
                    <dt>Students (matching)</dt>
                    <dd>Your name, e-mail and password (stored only as a secure hash). Your matching form: date of birth, citizenship, home province, DNSC campus, program, year level and enrolment status, grades and conduct, your household's income range (never an exact amount), priority groups you tick (for example Listahanan, 4Ps, person with disability, Indigenous Peoples), farmer or fisherfolk registries, other scholarships you receive, skills and student roles, and the provider-specific questions. The form asks only what a scholarship rule checks: no essays, contact number, street address or details about your family members. We also keep your matching results and the date you agreed to this policy.</dd>
                </div>
                <div>
                    <dt>Scholars (monitoring)</dt>
                    <dd>The details your scholarship provider records when it adds you: name, e-mail, student number, Scholar ID, campus, program, year level, birthday, gender, mobile number, address, grant type and stipend. Also the maintaining documents you upload, your stipend records, your correction requests with the provider's replies, and updates sent to you.</dd>
                </div>
                <div>
                    <dt>Providers and the administrator</dt>
                    <dd>Your name, work e-mail and password (as a secure hash), and what you do in your portal (for example edits to a scholarship, which are kept as revisions).</dd>
                </div>
                <div>
                    <dt>Anyone who writes to us</dt>
                    <dd>The name, e-mail and message you send through the Contact page or a portal's Help form, and, for feedback on the About page, your star rating and the parts of BAKR you say you used. Feedback is shown publicly (your first name, rating and comment only) only if you ticked "share" and the BAKR team approved it.</dd>
                </div>
            </dl>
        </section>

        <section id="use" aria-labelledby="h-use">
            <h2 id="h-use">3. Why we use it</h2>
            <ul>
                <li><strong>To match you with scholarships.</strong> BAKR's rules check each scholarship's published limits, and the AI matcher reviews your matching form against each scholarship's requirements.</li>
                <li><strong>To help providers monitor their scholars:</strong> maintaining documents, stipend releases, scholar status and correction requests.</li>
                <li><strong>To contact you:</strong> sign-in details, password reset links, replies to your messages and updates from your provider.</li>
                <li><strong>To show anonymous totals</strong> on the About page. Only combined counts are shown, and any group of fewer than 10 people is hidden.</li>
                <li><strong>To keep BAKR safe:</strong> sign-in checks, limits on repeated attempts and preventing misuse.</li>
            </ul>
            <p>We never sell your information or use it for advertising.</p>
        </section>

        <section id="ai" aria-labelledby="h-ai">
            <h2 id="h-ai">4. The AI matcher</h2>
            <p>When you save your matching form, BAKR sends a <strong>de-identified</strong> copy of your answers to Google's Gemini AI, which compares them with each scholarship's published requirements and writes your explanations. It never receives your name, e-mail, mobile number, address, exact birth date (only your age) or account number, and names, e-mails, phone numbers and links are removed from anything sent. The AI can only make a result stricter: it can never pass a limit that BAKR's rules found you do not meet.</p>
            <p>BAKR currently uses Google's free Gemini service. Under Google's terms for that service, Google may use what it receives to improve its products, and people at Google may review it. That is why BAKR sends only de-identified answers. Scholar records (personal details, documents and stipends) are never sent to an AI.</p>
        </section>

        <section id="share" aria-labelledby="h-share">
            <h2 id="h-share">5. Who can see your information</h2>
            <ul>
                <li><strong>You</strong> see your own answers, matches, documents and records.</li>
                <li><strong>Scholarship providers</strong> see only their own scholars. They never see students' matching form answers or matching results.</li>
                <li><strong>The BAKR administrator</strong> manages accounts and reads messages sent to the BAKR team. The administrator sees names and e-mails of accounts, but not your matching form answers, uploaded documents or the personal details your provider records. No one can see your password.</li>
                <li><strong>Service providers</strong> who run parts of BAKR for us: Google (the Gemini AI matcher, and Gmail, which delivers BAKR's e-mails) and the server that hosts BAKR.</li>
                <li><strong>Authorities</strong>, only when the law requires it.</li>
            </ul>
        </section>

        <section id="basis" aria-labelledby="h-basis">
            <h2 id="h-basis">6. Our lawful basis</h2>
            <p>Matching, including the AI matcher, is based on your <strong>consent</strong>, which you give at the end of the matching form (Sections 12(a) and 13(a) of the Data Privacy Act; some answers, such as your age, disability or Indigenous Peoples group, are sensitive personal information). Scholar monitoring is based on what is needed to manage the scholarship you hold and on the provider's legitimate interest (Sections 12(b) and 12(f)). Messages are handled because you sent them to us.</p>
        </section>

        <section id="keep" aria-labelledby="h-keep">
            <h2 id="h-keep">7. How we protect and keep it</h2>
            <ul>
                <li>Application answers are stored encrypted, and passwords only as secure hashes.</li>
                <li>Uploaded documents are kept in private storage that only you and your provider can open.</li>
                <li>Every page checks who you are and shows only your own records; installed BAKR apps never keep copies of pages on your device.</li>
            </ul>
            <p>We keep your information while your account exists. Each time you save your matching form a new version is kept, so you can see what you answered before. To have your account and data deleted, write to us (see Section 11). Records your provider needs for a scholarship you hold may also be kept by that provider under its own rules.</p>
        </section>

        <section id="cookies" aria-labelledby="h-cookies">
            <h2 id="h-cookies">8. Cookies and your browser</h2>
            <p>BAKR uses only the cookies it needs to work: a session cookie that keeps you signed in, a security token for forms, and, if you tick "Keep me signed in", a cookie that remembers your sign-in. Your browser also remembers small preferences such as light or dark mode. There are no advertising or tracking cookies.</p>
        </section>

        <section id="rights" aria-labelledby="h-rights">
            <h2 id="h-rights">9. Your rights</h2>
            <p>Under the Data Privacy Act you have the right to:</p>
            <ul class="pp-rights">
                <li><strong>Be informed</strong> of how your data is used (this policy).</li>
                <li><strong>Access</strong> your data. Students: see My profile, or view and print your submitted form.</li>
                <li><strong>Correct</strong> it. Students: update your matching form at any time. Scholars: use "Request a correction" on your Profile.</li>
                <li><strong>Object</strong> to processing and <strong>withdraw your consent</strong>. Without consent BAKR cannot match you, but your account stays yours.</li>
                <li><strong>Have it erased or blocked</strong> when it is no longer needed or was processed unlawfully.</li>
                <li><strong>Data portability:</strong> get a copy of your data in a form you can keep.</li>
                <li><strong>Claim damages</strong> for harm caused by inaccurate or unlawfully used data.</li>
                <li><strong>File a complaint</strong> with the National Privacy Commission (<a href="https://privacy.gov.ph" target="_blank" rel="noopener noreferrer">privacy.gov.ph</a>).</li>
            </ul>
        </section>

        <section id="minors" aria-labelledby="h-minors">
            <h2 id="h-minors">10. Students under 18</h2>
            <p>Some incoming first-year students are under 18. If that is you, please read this policy with your parent or guardian before you agree to it.</p>
        </section>

        <section id="changes" aria-labelledby="h-changes">
            <h2 id="h-changes">11. Changes and contact</h2>
            <p>When we change this policy we update the date at the top, and you agree to the current version the next time you save your matching form.</p>
            <p>For questions, requests about your data or complaints, e-mail the BAKR team at <a href="mailto:{{ $email }}">{{ $email }}</a> or use the <a href="{{ route('contact') }}">Contact page</a>. Scholars can also contact their scholarship provider.</p>
        </section>
    </article>
</div>
@endsection
