@extends('scholar.layout')
@section('title', 'Profile | BAKR')

{{-- Scholar portal › Profile, modelled on the scholar prototype's Profile view (scholarDashboardF):
     an identity summary first (avatar, name, program/year, student number, scholarship), then the
     details grouped in cards, two per row. Read-only: the provider keeps these records, so the
     prototype's "Edit profile" becomes "Request a correction" (#corrections, owner 2026-11-20): it goes
     to the provider of the chosen scholarship (provider › Messages), never to the admin, and their
     replies show here.
     Since 2026-11-20 the provider also records birthday + gender (Personal) and mobile number + address
     (Contact), and each scholarship's Scholar ID; $personal = the first record that has each detail. --}}

@php
    $initials = collect(preg_split('/\s+/', trim($user->name)))->filter()->take(2)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('');
@endphp

@section('hero_title')Your <em>profile</em>@endsection
@section('hero_sub', 'Your scholar information, as your provider recorded it.')
@section('hero_actions')
    @if ($scholars->isNotEmpty())
        <a class="dash-btn is-soft" href="#corrections"><i class="bi bi-pencil-square" aria-hidden="true"></i>Request a correction</a>
    @endif
    <a class="dash-btn is-soft" href="{{ route('scholar.settings') }}"><i class="bi bi-gear" aria-hidden="true"></i>Settings</a>
@endsection

@section('portal')
{{-- ============ IDENTITY SUMMARY ============ --}}
<section class="dash-card sc-id" aria-labelledby="id-name">
    <span class="sc-id-avatar" aria-hidden="true">{{ $initials ?: 'S' }}</span>
    <div class="sc-id-text">
        <h2 class="sc-id-name" id="id-name">{{ $user->name }}</h2>
        <div class="dash-meta is-id">
            @if ($school?->program)<span><i class="bi bi-book" aria-hidden="true"></i>{{ $school->program }}</span>@endif
            @if ($school?->campusLabel())<span><i class="bi bi-geo-alt" aria-hidden="true"></i>{{ $school->campusLabel() }}</span>@endif
            @if ($school?->yearLabel())<span><i class="bi bi-mortarboard" aria-hidden="true"></i>{{ $school->yearLabel() }}</span>@endif
            <span><i class="bi bi-person-vcard" aria-hidden="true"></i>Student no.: {{ $school?->student_number ?: '—' }}</span>
        </div>
    </div>
    <div class="sc-id-program">
        <span class="sc-id-label">{{ \Illuminate\Support\Str::plural('Scholarship', max(1, $scholars->count())) }}</span>
        @forelse ($scholars as $sc)
            <strong>{{ $sc->scholarship->name }}</strong>
        @empty
            <strong>—</strong>
        @endforelse
    </div>
</section>

{{-- ============ DETAILS, GROUPED ============
     Cards two per row (one column on phones). The ids are link targets (notifications open e.g. #personal). --}}
<div class="sc-profile-grid">
    <section class="dash-card" id="personal" aria-labelledby="pf-personal">
        <h2 class="dash-card-title" id="pf-personal"><i class="bi bi-person" aria-hidden="true"></i>Personal information</h2>
        <p class="sc-card-sub">Who you are</p>
        <dl class="sc-facts">
            {{-- Name in parts (2026-11-23); an account from before then shows a best guess until the provider saves it. --}}
            @php $nameNow = $user->first_name ? $user->only(\App\Support\PersonName::FIELDS) : \App\Support\PersonName::split($user->name); @endphp
            <div><dt>First name</dt><dd>{{ $nameNow['first_name'] ?: '—' }}</dd></div>
            <div><dt>Middle name</dt><dd>{{ $nameNow['middle_name'] ?: '—' }}</dd></div>
            <div><dt>Surname</dt><dd>{{ $nameNow['last_name'] ?: '—' }}</dd></div>
            <div><dt>Suffix</dt><dd>{{ $nameNow['suffix'] ?: '—' }}</dd></div>
            <div><dt>Birthday</dt><dd>{{ $personal['birthdate'] ?? '—' }}</dd></div>
            <div><dt>Gender</dt><dd>{{ $personal['gender'] ?? '—' }}</dd></div>
            <div><dt>Account</dt><dd>Scholar account</dd></div>
            <div><dt>Member since</dt><dd>{{ $user->created_at?->format('M j, Y') ?? '—' }}</dd></div>
        </dl>
    </section>

    <section class="dash-card" id="contact" aria-labelledby="pf-contact">
        <h2 class="dash-card-title" id="pf-contact"><i class="bi bi-envelope" aria-hidden="true"></i>Contact information</h2>
        <p class="sc-card-sub">How your provider and BAKR reach you</p>
        <dl class="sc-facts">
            <div><dt>E-mail</dt><dd>{{ $user->email }}</dd></div>
            <div><dt>Mobile number</dt><dd>{{ $personal['mobile'] ?? '—' }}</dd></div>
            <div><dt>Address</dt><dd>{{ $personal['address'] ?? '—' }}</dd></div>
            <div><dt>Sign-in</dt><dd>Your e-mail, at the Scholar login</dd></div>
        </dl>
    </section>

    <section class="dash-card" id="school" aria-labelledby="pf-school">
        <h2 class="dash-card-title" id="pf-school"><i class="bi bi-building" aria-hidden="true"></i>School information</h2>
        <p class="sc-card-sub">Your current academic information</p>
        <dl class="sc-facts">
            <div><dt>School</dt><dd>Davao del Norte State College</dd></div>
            <div><dt>Student number</dt><dd>{{ $school?->student_number ?: '—' }}</dd></div>
            <div><dt>Campus</dt><dd>{{ $school?->campusLabel() ?: '—' }}</dd></div>
            <div><dt>Program</dt><dd>{{ $school?->program ?: '—' }}</dd></div>
            <div><dt>Year level</dt><dd>{{ $school?->yearLabel() ?? '—' }}</dd></div>
        </dl>
    </section>

    <section class="dash-card" id="scholarship" aria-labelledby="pf-scholarship">
        <h2 class="dash-card-title" id="pf-scholarship"><i class="bi bi-award" aria-hidden="true"></i>Scholarship information</h2>
        <p class="sc-card-sub">What you hold, and since when</p>
        @forelse ($scholars as $sc)
            <dl class="sc-facts {{ $loop->first ? '' : 'pv-gap' }}">
                <div><dt>Scholarship</dt><dd>{{ $sc->scholarship->name }}</dd></div>
                <div><dt>Scholar ID</dt><dd>@if ($sc->scholar_code)<span class="pv-code">{{ $sc->scholar_code }}</span>@else Not recorded yet @endif</dd></div>
                <div><dt>Provider</dt><dd>{{ $sc->scholarship->provider }}</dd></div>
                <div><dt>Status</dt><dd><span class="dash-status {{ $sc->statusTone() }}">{{ $sc->statusLabel() }}</span></dd></div>
                @if ($sc->grantLabel())<div><dt>Grant type</dt><dd>{{ $sc->grantLabel() }}</dd></div>@endif
                <div><dt>Scholar since</dt><dd>{{ $sc->started_term ?: '—' }}</dd></div>
            </dl>
        @empty
            <p class="dash-empty">No scholarship is linked to your account yet.</p>
        @endforelse
    </section>
</div>

{{-- ============ REQUEST A CORRECTION (to the provider) ============ --}}
@if ($scholars->isNotEmpty())
@php
    $cErrors = $errors->getBag('correction');
    $oldScholar = (int) old('scholar_id', $scholars->first()->id);
@endphp
<div class="sc-profile-grid" id="corrections">
    <section class="dash-card" aria-labelledby="pf-correction">
        <h2 class="dash-card-title" id="pf-correction"><i class="bi bi-pencil-square" aria-hidden="true"></i>Request a correction</h2>
        <p class="sc-card-sub">Your provider keeps these details, so your request goes straight to them</p>

        @if ($cErrors->any())
            <div class="dash-flash is-error" role="alert"><i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i><ul>@foreach ($cErrors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
        @endif

        <form method="POST" action="{{ route('scholar.profile.correction') }}" class="dash-stack" data-busy="Sending your request…" data-busy-sub="It goes to your provider">
            @csrf
            @if ($scholars->count() > 1)
                <div class="dash-field">
                    <label for="c-scholar">Which scholarship?</label>
                    <select id="c-scholar" name="scholar_id" required>
                        @foreach ($scholars as $sc)
                            <option value="{{ $sc->id }}" @selected($oldScholar === $sc->id)>{{ $sc->scholarship->name }} ({{ $sc->scholarship->provider }})</option>
                        @endforeach
                    </select>
                    <small>Only that scholarship's provider receives it.</small>
                </div>
            @else
                <input type="hidden" name="scholar_id" value="{{ $scholars->first()->id }}">
                <div class="dash-meta"><span><i class="bi bi-send" aria-hidden="true"></i>Goes to {{ $scholars->first()->scholarship->provider }} ({{ $scholars->first()->scholarship->name }})</span></div>
            @endif
            <div class="dash-field">
                <label for="c-topic">What needs correcting?</label>
                <select id="c-topic" name="topic" required>
                    @foreach ($topics as $key => $label)
                        <option value="{{ $key }}" @selected(old('topic', 'personal') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="dash-field">
                <label for="c-message">What is wrong, and what should it be?</label>
                <textarea id="c-message" name="message" rows="5" minlength="10" maxlength="2000" required class="{{ $cErrors->has('message') ? 'is-invalid' : '' }}">{{ old('message') }}</textarea>
                <small>For example: "My birthday shows Jul 1, 2004; it should be Jun 1, 2004." Please don't include your password.</small>
            </div>
            <div><button type="submit" class="dash-btn"><i class="bi bi-send" aria-hidden="true"></i>Send to my provider</button></div>
        </form>
    </section>

    <section class="dash-card" id="requests" aria-labelledby="pf-requests">
        <h2 class="dash-card-title" id="pf-requests"><i class="bi bi-chat-left-text" aria-hidden="true"></i>Your requests</h2>
        <p class="sc-card-sub">What you asked, and your provider's answer</p>
        @forelse ($requests as $r)
            <article class="{{ $loop->first ? '' : 'pv-gap' }}" aria-label="Request of {{ $r->created_at?->format('M j, Y') }}">
                <div class="am-head">
                    <strong class="am-head-title">{{ $r->topicShort() }}</strong>
                    @if ($r->reply)
                        <span class="dash-status is-ok">Answered</span>
                    @elseif ($r->handled_at)
                        <span class="dash-status is-ok">Done</span>
                    @else
                        <span class="dash-status is-info">Waiting for your provider</span>
                    @endif
                </div>
                <div class="dash-meta">
                    <span><i class="bi bi-building" aria-hidden="true"></i>{{ $r->scholar->scholarship->provider }}</span>
                    <span><i class="bi bi-calendar3" aria-hidden="true"></i>{{ $r->created_at?->format('M j, Y') }}</span>
                </div>
                <p class="pv-msg">{{ $r->message }}</p>
                @if ($r->reply)
                    <div class="am-sent" aria-label="Reply from {{ $r->scholar->scholarship->provider }}">
                        <p class="am-sent-title"><i class="bi bi-reply-fill" aria-hidden="true"></i>{{ $r->scholar->scholarship->provider }} replied {{ $r->replied_at?->format('M j, Y g:i A') }}</p>
                        <p class="pv-msg">{{ $r->reply }}</p>
                    </div>
                @endif
            </article>
        @empty
            <p class="dash-empty">You haven't asked for a correction yet.</p>
        @endforelse
    </section>
</div>
@endif

<p class="sc-hint"><i class="bi bi-info-circle" aria-hidden="true"></i> Your provider keeps these details. @if ($scholars->isNotEmpty())Something wrong? <a href="#corrections">Request a correction</a>.@else Something wrong? Contact your provider.@endif</p>
@endsection
