@extends('student.layout')
@section('title', 'Scholarship matching form | BAKR')

@section('hero_title')
{!! $application ? 'Update your <em>matching form</em>' : 'Scholarship <em>matching form</em>' !!}
@endsection
@section('hero_sub')
Fill in your personal, academic and family information. BAKR's rules check the published limits, and the Gemini AI matcher reads your whole form against the eligibility and requirements of every scholarship. No documents to upload. It takes about 5 minutes.
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/bakr-form.css') }}">
@endpush

@section('portal')

@if ($errors->any())
    <div class="sp-flash err" role="alert"><i class="bi bi-exclamation-triangle-fill"></i>
        <span>Some answers need your attention. They are marked in red below.</span></div>
@endif

{{-- Form header: like the top of a paper form. Name and e-mail come from the account (read-only);
     the reference number is derived from the saved version, nothing extra is stored. --}}
<section class="sp-card bf-head" aria-labelledby="bfTitle">
    <div class="bf-head-top">
        <div>
            <span class="bf-kicker">Davao del Norte State College · BAKR</span>
            <h2 id="bfTitle">Scholarship Matching Form</h2>
        </div>
        <dl class="bf-meta">
            <div><dt>Reference no.</dt><dd>{{ $application ? $application->referenceNumber() : 'Given when you submit' }}</dd></div>
            <div><dt>Semester</dt><dd>{{ $term }}</dd></div>
            @if ($application)
                <div><dt>Version</dt><dd>{{ $application->version }}, {{ $application->submitted_at->format('M j, Y') }}</dd></div>
            @endif
        </dl>
    </div>
    <dl class="bf-student">
        <div><dt>Student name</dt><dd>{{ $user->name }}</dd></div>
        <div><dt>E-mail</dt><dd>{{ $user->email }}</dd></div>
    </dl>
    <p class="bf-disclaimer"><i class="bi bi-info-circle" aria-hidden="true"></i>
        <span>This is BAKR's <strong>matching form</strong>, not the official application form of any scholarship provider. It tells you which scholarships fit you. To actually apply, follow each provider's own process shown in your matches.</span></p>
    <p class="bf-disclaimer"><i class="bi bi-shield-lock" aria-hidden="true"></i>
        <span><strong>What the AI matcher sees:</strong> your answers without your name, e-mail or exact birth date (only your age). Everything is stored encrypted and only you can see it.</span></p>
    @if ($fromVersion)
        <p class="bf-disclaimer is-version"><i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i>
            <span><strong>Filled in with your answers from version {{ $fromVersion->version }}</strong> ({{ $fromVersion->submitted_at->format('M j, Y') }}).
            Check them, then submit to save them as a new version. Nothing changes until you submit.
            <a href="{{ route('student.application.show') }}">Use my current answers instead</a>.</span></p>
    @elseif ($application)
        <p class="bf-disclaimer"><i class="bi bi-clock-history" aria-hidden="true"></i>
            <span>Saving changes keeps version {{ $application->version }} and checks your matches again.
            <a href="{{ route('student.application.print') }}">View or print the submitted form</a>.</span></p>
    @endif
</section>

<form method="POST" action="{{ route('student.application.store') }}" id="appForm" novalidate data-busy="Saving your matching form…" data-busy-sub="Checking it against each scholarship's requirements">
    @csrf

    {{-- Sticky part bar: jump to a part, see which are finished. Every part is on this page. --}}
    <div class="sp-stepbar">
        <ol class="sp-steps bf-steps" aria-label="Parts of this form" id="stepper">
            @foreach (array_values($sections) as $i => $section)
                <li><button type="button" data-goto="{{ $i }}" aria-current="{{ $i === 0 ? 'step' : 'false' }}"><b>{{ $section['part'] }}</b><span>{{ $section['title'] }}</span></button></li>
            @endforeach
        </ol>
        <div class="sp-bar" aria-hidden="true"><span id="progress" style="--w: 0%"></span></div>
    </div>

    @foreach (array_values($sections) as $i => $section)
        <section class="sp-card sp-section nx-reveal" data-step="{{ $i }}" data-part="Part {{ $section['part'] }}" id="section-{{ $i }}" aria-labelledby="sec-{{ $i }}">
            <h2 id="sec-{{ $i }}"><i class="bi {{ $section['icon'] }}" aria-hidden="true"></i> <span><small class="bf-part">Part {{ $section['part'] }}</small>{{ $section['title'] }}</span></h2>
            <p class="sp-sub">{{ $section['intro'] }}</p>
            <div class="sp-fields">
                @php $n = 0; @endphp
                @foreach ($section['fields'] as $key => $f)
                    {{-- The GWA scale belongs to the GWA item, so it gets no number of its own (same as the printable copy). --}}
                    @include('student.partials.field', $key === 'college_gwa_scale' ? ['key' => $key, 'f' => $f, 'saved' => $saved] : ['key' => $key, 'f' => $f, 'saved' => $saved, 'num' => ($i + 1) . '.' . (++$n)])
                @endforeach
            </div>
        </section>
    @endforeach

    {{-- Certification: the student types their name as a signature. Checked against the account, never stored. --}}
    <section class="sp-card sp-finish bf-cert nx-reveal" aria-labelledby="certTitle">
        <h2 id="certTitle"><span class="bf-part">Part {{ \App\Matching\ApplicationSchema::certificationPart() }}</span> Certification</h2>
        <p class="bf-cert-text">I certify that the information I gave in this form is true and correct to the best of my knowledge. I understand that wrong answers can give me wrong matches, and that each scholarship provider will check my documents when I apply to them.</p>

        <div class="bf-sign">
            <div class="sp-field {{ $errors->has('certify_name') ? 'has-error' : '' }}" data-field="certify_name" data-type="text" data-required="1" data-label="Signature (full name)" data-account-name="{{ $user->name }}">
                <label for="f-certify_name">Type your full name as your signature<span class="sp-req" aria-hidden="true">*</span></label>
                <input class="sp-input bf-sign-input" type="text" id="f-certify_name" name="certify_name" value="{{ old('certify_name') }}" maxlength="255" autocomplete="name" placeholder="{{ $user->name }}" aria-describedby="certHelp">
                <p class="sp-help" id="certHelp">Exactly as on your account: <strong>{{ $user->name }}</strong></p>
                @if ($errors->has('certify_name'))<p class="sp-err" role="alert">{{ $errors->first('certify_name') }}</p>@endif
            </div>
            <div class="bf-date"><span>Date</span><strong>{{ now()->format('F j, Y') }}</strong></div>
        </div>

        {{-- Privacy Policy agreement (owner, 2026-11-21): required on every submit, so the student always agrees to the
             current policy. The policy link opens in a new tab, so nothing typed here is lost. --}}
        <div class="sp-field bf-privacy {{ $errors->has('privacy_consent') ? 'has-error' : '' }}" data-field="privacy_consent" data-type="checkbox" data-required="1" data-label="Privacy Policy agreement">
            <span class="sp-label" id="lbl-privacy"><i class="bi bi-shield-lock" aria-hidden="true"></i> Privacy Policy agreement<span class="sp-req" aria-hidden="true">*</span></span>
            <p class="bf-privacy-text">BAKR processes your answers under the <strong>Data Privacy Act of 2012 (Republic Act No. 10173)</strong>. Your answers are stored encrypted and only you can see them. A copy <strong>without your name, e-mail or exact birth date</strong> is sent to Google's Gemini AI to compare with each scholarship's requirements. Scholarship providers never see your answers, and public pages show only anonymous totals. You can see, correct or ask us to delete your data at any time.</p>
            <div class="sp-choices stack" role="group" aria-labelledby="lbl-privacy">
                <label class="sp-choice box"><input type="checkbox" name="privacy_consent" value="1" {{ old('privacy_consent') ? 'checked' : '' }}><span>I have read and agree to the BAKR Privacy Policy, and I consent to BAKR processing my answers, including the sensitive ones, to match me with scholarships as described there.</span></label>
            </div>
            <p class="sp-help"><a href="{{ route('privacy') }}" target="_blank" rel="noopener">Read the Privacy Policy <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i></a> (opens in a new tab)@if ($hasConsent) · You last agreed on {{ $consentAt?->format('M j, Y') }}@endif</p>
            @if ($errors->has('privacy_consent'))<p class="sp-err" role="alert">{{ $errors->first('privacy_consent') }}</p>@endif
        </div>

        <div class="sp-finish-row">
            <p id="progressText" class="sp-progress-text" aria-live="polite"></p>
            {{-- With JavaScript this opens the review first; without it, it submits straight away. --}}
            <button type="submit" class="sp-btn sp-btn-gold" data-review-label="Review and submit"><i class="bi bi-clipboard-check" aria-hidden="true"></i> <span>{{ $application ? 'Save and match again' : 'Submit my matching form' }}</span></button>
        </div>
    </section>

    {{-- Review: filled in by student-form.js from the answers on this page (text only). --}}
    <dialog class="dash-dialog bf-review" id="reviewDialog" aria-labelledby="reviewTitle">
        <div class="dash-dialog-head bf-review-head">
            <div>
                <span class="dash-kicker">Step before submitting</span>
                <h2 id="reviewTitle">Review your <em>answers</em></h2>
            </div>
            <button type="button" class="dash-dialog-close" data-close-dialog aria-label="Close"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
        </div>
        <div class="dash-dialog-body">
            <p class="bf-review-lead">Check every part. If something is wrong, go back and change it before you submit.</p>
            <div id="reviewBody" class="bf-review-body"></div>
        </div>
        <div class="dash-dialog-foot">
            <button type="button" class="dash-btn is-soft" data-close-dialog><i class="bi bi-pencil" aria-hidden="true"></i> Go back and edit</button>
            <button type="button" class="dash-btn" id="reviewConfirm"><i class="bi bi-send-check" aria-hidden="true"></i> Confirm and submit</button>
        </div>
    </dialog>
</form>
@endsection

@push('scripts')
    <script src="{{ asset('js/student-form.js') }}"></script>
@endpush
