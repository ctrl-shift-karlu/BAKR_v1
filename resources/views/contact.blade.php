@extends('layouts.app')

@section('title', 'Contact Us | BAKR')
@section('description', 'Send a message to the BAKR team: your questions, feedback, problems or corrections to scholarship information.')
@section('page', 'contact')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/bakr-pages.css') }}">
    <link rel="stylesheet" href="{{ asset('css/bakr-contact.css') }}">
@endpush

@section('content')
<header class="bk-page-hero" style="--page-hero-image: url('{{ asset('images/img/opt/dnsc_gate.jpg') }}')">
    <div class="container">
        <ol class="bk-crumbs" aria-label="Breadcrumb">
            <li><a href="{{ url('/') }}">Home</a></li>
            <li aria-current="page">Contact Us</li>
        </ol>
        <h1>Contact <em>Us</em></h1>
        <p>Have a question about BAKR, a problem you ran into, or an idea that would make scholarships clearer for the next student? Write to us.</p>
    </div>
</header>

<div class="container bk-page-body">
    <div class="bk-contact-cards">
        <a class="bk-contact-card" href="mailto:bakr.dnsc@gmail.com">
            <i class="bi bi-envelope-fill" aria-hidden="true"></i>
            <div><strong>Email</strong><span>bakr.dnsc@gmail.com</span></div>
        </a>
        <a class="bk-contact-card" href="https://www.google.com/maps/search/?api=1&query=Davao+del+Norte+State+College" target="_blank" rel="noopener noreferrer">
            <i class="bi bi-geo-alt-fill" aria-hidden="true"></i>
            <div><strong>Visit</strong><span>Davao del Norte State College</span></div>
        </a>
        <div class="bk-contact-card">
            <i class="bi bi-building-check" aria-hidden="true"></i>
            <div><strong>Official applications</strong><span>BAKR does not process applications. You submit your final application to the DNSC Scholarship and Grants Unit or the official provider.</span></div>
        </div>
    </div>
</div>

{{-- ============================================================
     MESSAGE FORM — saved in the database and emailed to the BAKR team
     ============================================================ --}}
<section class="bk-msg" id="message" aria-labelledby="msgTitle">
    <div class="container bk-msg-grid">
        <div class="bk-msg-form nx-reveal">
            <span class="bk-msg-kicker">Write to us</span>
            <h2 id="msgTitle">Send a message</h2>
            <p class="bk-msg-intro">Pick a topic, tell us what is on your mind, and we will reply to your e-mail.</p>

            @if (session('contact_sent'))
                <div class="bk-msg-alert is-ok" role="status" tabindex="-1" id="msgSent">
                    <i class="bi bi-check-circle-fill" aria-hidden="true"></i>
                    <div><strong>Message sent. Thank you!</strong><span>We read every message and will reply to the e-mail you gave us.</span></div>
                </div>
            @endif
            @if ($errors->any())
                <div class="bk-msg-alert is-error" role="alert" tabindex="-1" id="msgErrors">
                    <i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i>
                    <div><strong>Please check the highlighted fields.</strong></div>
                </div>
            @endif

            <form id="contactForm" method="POST" action="{{ url('contact') }}" novalidate>
                @csrf
                <fieldset class="bk-topics">
                    <legend>What is this about?</legend>
                    @foreach (\App\Models\ContactMessage::TOPICS as $value => $label)
                        <label class="bk-topic">
                            <input type="radio" name="topic" value="{{ $value }}" @checked(old('topic', 'question') === $value) required>
                            <span>{{ $label }}</span>
                        </label>
                    @endforeach
                    @error('topic') <div class="bk-field-error">{{ $message }}</div> @enderror
                </fieldset>

                <div class="bk-msg-row">
                    <div>
                        <label for="cName">Your name</label>
                        <input type="text" id="cName" name="name" value="{{ old('name') }}" maxlength="80" autocomplete="name" required @error('name') aria-invalid="true" @enderror>
                        @error('name') <div class="bk-field-error">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label for="cEmail">Email for our reply</label>
                        <input type="email" id="cEmail" name="email" value="{{ old('email') }}" maxlength="120" autocomplete="email" required @error('email') aria-invalid="true" @enderror>
                        @error('email') <div class="bk-field-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="bk-feedback-only" id="feedbackExtras" hidden>
                    <label for="cRating">How was your experience? <span class="bk-opt">(optional)</span></label>
                    <select id="cRating" name="rating">
                        <option value="">Select rating</option>
                        @foreach (array_reverse(\App\Models\ContactMessage::RATINGS, true) as $n => $t)
                            <option value="{{ $n }}" @selected((string) old('rating') === (string) $n)>{{ $n }} — {{ $t }}</option>
                        @endforeach
                    </select>
                    @error('rating') <div class="bk-field-error">{{ $message }}</div> @enderror
                    <label class="bk-check">
                        <input type="checkbox" name="share_publicly" value="1" @checked(old('share_publicly'))>
                        <span>You may show my feedback on this page after your team approves it. Show only my first name, never my e-mail.</span>
                    </label>
                </div>

                <div>
                    <label for="cMessage">Your message</label>
                    <textarea id="cMessage" name="message" rows="6" maxlength="2000" required @error('message') aria-invalid="true" @enderror>{{ old('message') }}</textarea>
                    <div class="bk-msg-hint"><span>Please don't include your passwords or ID numbers. We use your name and e-mail only to reply (<a href="{{ route('privacy') }}">Privacy Policy</a>).</span><span id="cCount">0 / 2000</span></div>
                    @error('message') <div class="bk-field-error">{{ $message }}</div> @enderror
                </div>

                {{-- Honeypot: hidden from people, bots tend to fill it in. --}}
                <div class="bk-hp" aria-hidden="true"><label>Leave this empty <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>

                <button type="submit" class="nx-btn nx-btn-gold" id="cSubmit"><i class="bi bi-send" aria-hidden="true"></i> Send message</button>
            </form>
        </div>

        <aside class="bk-msg-side nx-reveal" aria-label="What happens next">
            <h3>What happens next</h3>
            <ol>
                <li><span>1</span><div><strong>It reaches the BAKR team</strong><small>Your message is saved and emailed to us straight away.</small></div></li>
                <li><span>2</span><div><strong>We reply by email</strong><small>Please use an address you check often.</small></div></li>
                <li><span>3</span><div><strong>Applications stay official</strong><small>For your scholarship application or its status, contact the DNSC Scholarship and Grants Unit or the provider.</small></div></li>
            </ol>
            <a class="bk-msg-mail" href="mailto:bakr.dnsc@gmail.com"><i class="bi bi-envelope-fill" aria-hidden="true"></i> Prefer your own email app? bakr.dnsc@gmail.com</a>
        </aside>
    </div>
</section>

@if ($feedback->isNotEmpty())
{{-- ============================================================
     STUDENT FEEDBACK — only feedback the student agreed to share AND staff approved
     ============================================================ --}}
<section class="bk-voices" id="feedback" aria-labelledby="voicesTitle">
    <div class="container">
        <div class="text-center mb-4">
            <span class="bk-msg-kicker">Student voice</span>
            <h2 id="voicesTitle">What students say</h2>
            @if ($average)
                <p class="bk-voices-avg"><strong>{{ number_format($average, 1) }}</strong> average from {{ $feedback->count() }} shared {{ \Illuminate\Support\Str::plural('review', $feedback->count()) }}</p>
            @endif
        </div>
        <div class="bk-voices-grid">
            @foreach ($feedback as $f)
                <article class="bk-voice">
                    @if ($f->rating)
                        <div class="bk-voice-stars" role="img" aria-label="{{ $f->rating }} out of 5 stars">
                            @for ($i = 1; $i <= 5; $i++)<i class="bi {{ $i <= $f->rating ? 'bi-star-fill' : 'bi-star' }}" aria-hidden="true"></i>@endfor
                        </div>
                    @endif
                    @if (filled($f->message))<p>{{ $f->message }}</p>@endif
                    <footer>{{ \Illuminate\Support\Str::before($f->name, ' ') }}</footer>
                </article>
            @endforeach
        </div>
    </div>
</section>
@endif

<section class="bk-cta-strip">
    <div class="container">
        <h2>Looking for quick answers?</h2>
        <p>You may find your answer in our FAQs: matching, requirements and how to apply.</p>
        <a href="{{ url('/') }}#faqs" class="nx-btn nx-btn-green"><i class="bi bi-question-circle" aria-hidden="true"></i> Read the FAQs</a>
    </div>
</section>
@endsection

@push('scripts')
    <script src="{{ asset('js/bakr-contact.js') }}"></script>
@endpush
