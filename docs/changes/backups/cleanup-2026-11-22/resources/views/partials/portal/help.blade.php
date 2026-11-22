{{-- Help page body shared by the student portal (student.help) and the scholar portal (scholar.help):
     About BAKR, the portal's own FAQs and a message form (name/e-mail come from the account).
     $faqs [[question, answer], …]   $faqTitle   $messageRoute (POST)   $topics   $audience ('student')
     Scholars: corrections to their details go to the provider (Profile › Request a correction), not here. --}}
<div class="dash-split">
    <div class="dash-stack">
        <section class="dash-card" id="about" aria-labelledby="about-title">
            <h2 class="dash-section-title" id="about-title">About BAKR</h2>
            @include('partials.portal.about-bakr', ['audience' => $audience ?? 'student'])
        </section>

        <section class="dash-card" aria-labelledby="faq-title">
            <h2 class="dash-section-title" id="faq-title">{{ $faqTitle }}</h2>
            <div class="help-faq">
                @foreach ($faqs as [$q, $a])
                    <details @if ($loop->first) open @endif>
                        <summary>{{ $q }}</summary>
                        <p>{{ $a }}</p>
                    </details>
                @endforeach
            </div>
        </section>
    </div>

    <section class="dash-card help-contact" id="message" aria-labelledby="msg-title">
        <h2 class="dash-section-title" id="msg-title">Message the BAKR team</h2>
        <p class="pv-muted">We will reply to <strong>{{ auth()->user()->email }}</strong>. For a decision about your scholarship, ask the provider or the DNSC Scholarship and Grants Unit.</p>
        @if (($audience ?? 'student') === 'scholar')
            <p class="pv-muted">Wrong details on your profile? Use <a href="{{ route('scholar.profile') }}#corrections">Profile › Request a correction</a>: it goes to your provider, who keeps those details.</p>
        @endif

        @if ($errors->any())
            <div class="dash-flash is-error" role="alert"><i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i><ul>@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
        @endif

        <form method="POST" action="{{ route($messageRoute) }}" class="dash-stack" data-busy="Sending your message…" data-busy-sub="The BAKR team will reply by e-mail">
            @csrf
            <div class="dash-field">
                <label for="topic">What is it about?</label>
                <select id="topic" name="topic" required>
                    @foreach ($topics as $key => $label)
                        <option value="{{ $key }}" @selected(old('topic', 'question') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="dash-field">
                <label for="message">Message</label>
                <textarea id="message" name="message" rows="6" minlength="10" maxlength="2000" required>{{ old('message') }}</textarea>
                <small>Please don't include your passwords or ID numbers. We use your message only to answer you (<a href="{{ route('privacy') }}" target="_blank" rel="noopener">Privacy Policy</a>).</small>
            </div>
            <div><button type="submit" class="dash-btn"><i class="bi bi-send" aria-hidden="true"></i>Send message</button></div>
        </form>
    </section>
</div>
