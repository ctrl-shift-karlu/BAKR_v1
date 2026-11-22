{{-- About BAKR, short version. Shown in the dashboards' "About BAKR" dialog and on the Help pages,
     so people learn what BAKR is without leaving their portal.
     $audience: 'student' (matching) | 'scholar' (monitoring) | 'provider'. --}}
@php($audience = $audience ?? 'student')
<div class="about-bakr">
    <p class="about-lead">BAKR is a scholarship guide built by students for <strong>Davao del Norte State College</strong>. It keeps the scholarships open to you in one place, shows you which ones fit, and helps you keep the scholarship you already have.</p>

    @if ($audience === 'scholar')
        <ol class="about-steps">
            <li><i class="bi bi-award" aria-hidden="true"></i><div><strong>Check your scholarship</strong><span>Your status, benefits and what you need to keep it.</span></div></li>
            <li><i class="bi bi-folder-check" aria-hidden="true"></i><div><strong>Submit requirements</strong><span>Upload each semester's maintaining documents; your provider reviews them.</span></div></li>
            <li><i class="bi bi-cash-coin" aria-hidden="true"></i><div><strong>Follow your stipends</strong><span>A semester's stipend is released once all its requirements are approved.</span></div></li>
        </ol>
    @else
        <ol class="about-steps">
            <li><i class="bi bi-person-plus" aria-hidden="true"></i><div><strong>Create an account</strong><span>A free student account keeps your answers private and yours.</span></div></li>
            <li><i class="bi bi-ui-checks" aria-hidden="true"></i><div><strong>Fill in your matching form</strong><span>Your personal, academic and family information. About five minutes, answered once.</span></div></li>
            <li><i class="bi bi-list-check" aria-hidden="true"></i><div><strong>See your matches</strong><span>Every scholarship, with what helps you, what blocks you and what to prepare.</span></div></li>
            <li><i class="bi bi-send-check" aria-hidden="true"></i><div><strong>Apply officially</strong><span>Follow the provider's own process. Once you are accepted, the provider adds you to BAKR as a scholar.</span></div></li>
        </ol>
    @endif

    <ul class="about-facts">
        @if ($audience === 'scholar')
            <li><i class="bi bi-lock" aria-hidden="true"></i><span><strong>Your files stay private.</strong> Only you and your scholarship's provider can open what you upload (Data Privacy Act of 2012).</span></li>
            <li><i class="bi bi-info-circle" aria-hidden="true"></i><span><strong>Your provider decides.</strong> Reviews, stipend dates and your scholar status come from your provider, not from BAKR.</span></li>
        @else
            <li><i class="bi bi-shield-check" aria-hidden="true"></i><span><strong>Rules and AI together.</strong> BAKR's rules check each scholarship's published limits, and an AI matcher (Google Gemini) reads your whole matching form against its requirements. You always see the stricter result, and the AI never sees your name, e-mail or birth date.</span></li>
            <li><i class="bi bi-lock" aria-hidden="true"></i><span><strong>Your data stays private.</strong> Matching form answers are encrypted and never sent to providers (Data Privacy Act of 2012).</span></li>
            @if ($audience === 'provider')
                <li><i class="bi bi-building" aria-hidden="true"></i><span><strong>For providers.</strong> You see your own scholarships only: the scholars you added, their stipends and requirements, and match counts (groups under 10 are hidden).</span></li>
            @else
                <li><i class="bi bi-info-circle" aria-hidden="true"></i><span><strong>BAKR is a guide.</strong> It does not award scholarships. The DNSC Scholarship and Grants Unit or the provider makes the final decision.</span></li>
            @endif
        @endif
        {{-- Every portal (About dialog + Help page): the Privacy Policy, Data Privacy Act of 2012 (2026-11-21). --}}
        <li><i class="bi bi-shield-lock" aria-hidden="true"></i><span><strong>Privacy Policy.</strong> What BAKR collects, who can see it and your rights under the Data Privacy Act of 2012 (RA 10173). <a href="{{ route('privacy') }}" target="_blank" rel="noopener">Read the Privacy Policy</a>.</span></li>
    </ul>
</div>
