@extends('provider.layout')

{{-- A scholar's profile, for their provider (layout 2026-11-20, after the scholar's own Profile page and
     My scholarships): an identity summary, the status card, then the details in cards two per row
     (School · Scholarship, Personal · Contact). Every card is read-only with its OWN Edit button
     (?edit=<section>): only that card turns into a form, and saving changes only its fields (hidden
     "section" input, ProviderScholarController::update / SECTIONS). A failed save comes back with that
     card open and the typed values. Requirements and stipend history sit below, side by side. --}}

@section('title', $s->displayName())
@section('photo', 'opt/student.jpg')
@section('kicker', 'Scholar profile')
@section('heading'){{ $s->displayName() }}@endsection
@section('sub', collect([$s->scholar_code, $s->scholarship->name, $s->statusLabel()])->filter()->implode(' · '))
@section('actions')
    <a class="dash-btn is-soft" href="{{ route('provider.scholars') }}"><i class="bi bi-arrow-left" aria-hidden="true"></i>All scholars</a>
@endsection

@section('content')
@php
    $payPill = ['released' => 'is-ok', 'pending' => 'is-warn'];
    $hasPay = $scope->has('disbursements');
    $hasReqs = $scope->has('requirements');
    $hasTypes = $s->scholarship->hasGrantTypes();
    $sections = \App\Http\Controllers\Provider\ProviderScholarController::SECTIONS;
    $asked = request('edit', old('section'));
    $editing = is_string($asked) && array_key_exists($asked, $sections) ? $asked : null;
    $readUrl = route('provider.scholars.show', $s->id);
    $editUrl = fn (string $key) => route('provider.scholars.show', [$s->id, 'edit' => $key]).'#'.$key;
    $notSet = '<span class="pv-attn">Not recorded yet</span>';
    $show = fn ($value) => filled($value) ? e($value) : $notSet;
    $saveNote = 'Saves only this card. The scholar sees the change on their profile.';
@endphp

@if ($errors->any() && $editing)
    <div class="dash-flash is-error" role="alert"><i class="bi bi-exclamation-triangle" aria-hidden="true"></i><span>Some fields need your attention. Nothing was saved yet.</span></div>
@endif

{{-- ============ IDENTITY SUMMARY ============ --}}
<section class="dash-card sp-id" aria-labelledby="sp-name">
    <span class="sp-id-avatar" aria-hidden="true">{{ $s->initials() ?: 'S' }}</span>
    <div class="sp-id-text">
        <h2 class="sp-id-name" id="sp-name">{{ $s->displayName() }}</h2>
        <div class="dash-meta is-id">
            @if ($s->program)<span><i class="bi bi-book" aria-hidden="true"></i>{{ $s->program }}</span>@endif
            @if ($s->campusLabel())<span><i class="bi bi-geo-alt" aria-hidden="true"></i>{{ $s->campusLabel() }}</span>@endif
            @if ($s->yearLabel())<span><i class="bi bi-mortarboard" aria-hidden="true"></i>{{ $s->yearLabel() }}</span>@endif
        </div>
        <div class="dash-meta is-id">
            <span class="dash-status {{ $s->statusTone() }}">{{ $s->statusLabel() }}</span>
            <span class="{{ $s->scholar_code ? '' : 'is-attn' }}"><i class="bi bi-person-vcard" aria-hidden="true"></i>Scholar ID: {{ $s->scholar_code ?: 'not recorded yet' }}</span>
            <span><i class="bi bi-hash" aria-hidden="true"></i>Student no.: {{ $s->student_number ?: '—' }}</span>
        </div>
    </div>
    <div class="sp-id-side">
        <span class="sp-id-label">Scholarship</span>
        <strong>{{ $s->scholarship->name }}</strong>
        @if ($s->grantLabel())<small>{{ $s->grantLabel() }}</small>@endif
        <small>Scholar since {{ $s->started_term ?: '—' }}</small>
    </div>
</section>

{{-- ============ STATUS ============
     Active / Inactive / Terminated. Every provider terminates and reinstates its own scholars.
     Inactive: automatic (overdue documents) for providers that collect documents in BAKR; set by
     hand by providers that check documents in person at the DNSC office ($hasReqs false). --}}
@php
    $overdue = $s->overdueRequirements();
    $statusForm = fn (string $to, string $label, string $icon, string $class = 'is-soft') => '<form method="POST" action="'.e(route('provider.scholars.status', $s->id)).'">'.csrf_field().method_field('PATCH')
        .'<input type="hidden" name="status" value="'.$to.'"><button type="submit" class="dash-btn '.$class.' is-sm"><i class="bi '.$icon.'" aria-hidden="true"></i>'.e($label).'</button></form>';
@endphp
<section class="dash-card pv-standing sp-standing is-{{ $s->standing() }}" aria-labelledby="standing-title">
    <h3 class="dash-card-title" id="standing-title"><i class="bi bi-shield-check" aria-hidden="true"></i>Scholarship status <span class="dash-status {{ $s->statusTone() }}">{{ $s->statusLabel() }}</span></h3>
    @switch ($s->standing())
        @case('active')
            <p class="pv-muted">In good standing{{ $hasReqs ? ': no maintaining document is past its due date.' : '.' }}</p>
            @break
        @case('inactive')
            @if ($s->isMarkedInactive())
                <p class="pv-muted">Marked Inactive by {{ $provider['name'] }}. Mark them active again once their maintaining documents are complete at the DNSC office.</p>
            @else
                <p class="pv-muted">Inactive because {{ $overdue->count() === 1 ? 'this maintaining document is' : 'these maintaining documents are' }} past due and not submitted. It turns Active again by itself once the scholar submits.</p>
                <ul class="pv-overdue">
                    @foreach ($overdue as $r)
                        <li><strong>{{ $r->name }}</strong><span class="dash-meta"><span><i class="bi bi-calendar3" aria-hidden="true"></i>{{ $r->term }}</span><span class="is-attn"><i class="bi bi-calendar-event" aria-hidden="true"></i>Due {{ $r->due_on?->format('M j, Y') }}</span><span>{{ $r->statusLabel() }}</span></span></li>
                    @endforeach
                </ul>
            @endif
            @break
        @case('terminated')
            <p class="pv-muted">Terminated. No stipends or requirement checklists are prepared for this scholar. Their records stay for your reference.</p>
    @endswitch

    @unless ($hasReqs)
        <p class="pv-office-note"><i class="bi bi-building" aria-hidden="true"></i><span>Maintaining documents for this scholarship are submitted <strong>in person at the official DNSC scholarship office</strong>, not through BAKR. Mark the scholar Inactive here when they are not complete.</span></p>
    @endunless

    <div class="pv-standing-actions">
        @if ($s->isTerminated())
            {!! $statusForm('active', 'Reinstate scholar', 'bi-arrow-counterclockwise') !!}
        @else
            @if (! $hasReqs)
                @if ($s->isMarkedInactive())
                    {!! $statusForm('active', 'Mark active', 'bi-check2-circle', '') !!}
                @else
                    {!! $statusForm('inactive', 'Mark inactive', 'bi-pause-circle', 'is-sun') !!}
                @endif
            @endif
            <a class="dash-btn is-danger-soft is-sm" href="#confirm-terminate" data-open-dialog="confirm-terminate"><i class="bi bi-x-circle" aria-hidden="true"></i>Terminate scholarship</a>
        @endif
        <small>Only {{ $provider['name'] }} can change the status of its own scholars.</small>
    </div>
</section>

{{-- ============ DETAILS, ONE CARD PER SECTION ============ --}}
<div class="sp-grid">
    {{-- ---------- School information ---------- --}}
    @if ($editing === 'school')
    <section class="dash-card sp-card is-editing" id="school" aria-labelledby="school-title">
        <form method="POST" action="{{ route('provider.scholars.update', $s->id) }}" class="dash-form-grid" novalidate>
            @csrf @method('PATCH')
            <input type="hidden" name="section" value="school">
            <h3 class="dash-card-title is-wide" id="school-title"><i class="bi bi-building" aria-hidden="true"></i>School information <span class="dash-pill is-warn ms-editing-pill"><i class="bi bi-pencil" aria-hidden="true"></i> Editing</span></h3>
            <div class="dash-field is-wide">
                <label for="scholar_code">Scholar ID</label>
                <input id="scholar_code" name="scholar_code" class="pv-code-input" maxlength="{{ \App\Models\Scholar::SCHOLAR_CODE_MAX }}" required autocomplete="off" spellcheck="false"
                       value="{{ old('scholar_code', $s->scholar_code) }}" aria-describedby="scholar_code-help">
                <small id="scholar_code-help">Type it exactly as your office writes it.</small>
                @error('scholar_code')<p class="dash-error">{{ $message }}</p>@enderror
            </div>
            <div class="dash-field">
                <label for="student_number">Student number</label>
                <input id="student_number" name="student_number" maxlength="30" required autocomplete="off" value="{{ old('student_number', $s->student_number) }}">
                @error('student_number')<p class="dash-error">{{ $message }}</p>@enderror
            </div>
            <div class="dash-field">
                <label for="year_level">Year level</label>
                <select id="year_level" name="year_level" required>
                    <option value="">Choose…</option>
                    @foreach (\App\Models\Scholar::YEAR_LEVELS as $key => $label)
                        <option value="{{ $key }}" @selected((string) old('year_level', $s->year_level) === (string) $key)>{{ $label }}</option>
                    @endforeach
                    @if ($s->year_level !== null && ! array_key_exists($s->year_level, \App\Models\Scholar::YEAR_LEVELS))
                        <option value="{{ $s->year_level }}" @selected((string) old('year_level', $s->year_level) === (string) $s->year_level)>{{ $s->yearLabel() }}</option>
                    @endif
                </select>
                @error('year_level')<p class="dash-error">{{ $message }}</p>@enderror
            </div>
            @include('provider.partials.campus-program', ['campus' => old('campus', $s->campusLabel()), 'program' => old('program', $s->program), 'required' => true])
            <div class="is-wide">@include('provider.partials.section-foot', ['cancel' => $readUrl.'#school', 'note' => $saveNote])</div>
        </form>
    </section>
    @else
    <section class="dash-card sp-card" id="school" aria-labelledby="school-title">
        <div class="sp-card-head">
            <h3 class="dash-card-title" id="school-title"><i class="bi bi-building" aria-hidden="true"></i>School information</h3>
            <a class="dash-btn is-soft is-sm" href="{{ $editUrl('school') }}"><i class="bi bi-pencil-square" aria-hidden="true"></i>Edit</a>
        </div>
        <dl class="sp-facts">
            <div><dt>Scholar ID</dt><dd>@if ($s->scholar_code)<span class="pv-code">{{ $s->scholar_code }}</span>@else{!! $notSet !!}@endif</dd></div>
            <div><dt>Student number</dt><dd>{!! $show($s->student_number) !!}</dd></div>
            <div><dt>Campus</dt><dd>{!! $show($s->campusLabel()) !!}</dd></div>
            <div><dt>Program</dt><dd>{!! $show($s->program) !!}</dd></div>
            <div><dt>Year level</dt><dd>{!! $show($s->yearLabel()) !!}</dd></div>
        </dl>
    </section>
    @endif

    {{-- ---------- Scholarship details ---------- --}}
    @if ($editing === 'scholarship')
    <section class="dash-card sp-card is-editing" id="scholarship" aria-labelledby="scholarship-title">
        <form method="POST" action="{{ route('provider.scholars.update', $s->id) }}" class="dash-form-grid" novalidate>
            @csrf @method('PATCH')
            <input type="hidden" name="section" value="scholarship">
            <h3 class="dash-card-title is-wide" id="scholarship-title"><i class="bi bi-award" aria-hidden="true"></i>Scholarship details <span class="dash-pill is-warn ms-editing-pill"><i class="bi bi-pencil" aria-hidden="true"></i> Editing</span></h3>
            <div class="dash-field is-wide">
                <label for="started_term">Scholar since</label>
                <select id="started_term" name="started_term" required aria-describedby="started_term-help">
                    @unless ($s->started_term)<option value="">Choose…</option>@endunless
                    @foreach (collect($startTerms)->push($s->started_term)->filter()->unique() as $t)
                        <option value="{{ $t }}" @selected(old('started_term', $s->started_term) === $t)>{{ $t }}</option>
                    @endforeach
                </select>
                <small id="started_term-help">The semester they started. They have no maintaining documents and no stipend before it. Moving it later removes empty checklist items from before it (uploaded files stay).</small>
                @error('started_term')<p class="dash-error">{{ $message }}</p>@enderror
            </div>
            @if ($hasTypes)
            <div class="dash-field is-wide">
                <label for="grant_type">Grant type</label>
                <select id="grant_type" name="grant_type" required>
                    @unless ($s->grant_type)<option value="">Choose…</option>@endunless
                    @foreach ($s->scholarship->grantOptions() as $key => $g)
                        <option value="{{ $key }}" @selected(old('grant_type', $s->grant_type) === $key)>{{ $g['label'] }}@if ($hasPay && \App\Models\Scholarship::grantAmountLabel($g)) · {{ \App\Models\Scholarship::grantAmountLabel($g) }}@endif</option>
                    @endforeach
                </select>
                @if ($hasPay)<small>Changing it also changes the stipend to that type's published amount.</small>@endif
                @error('grant_type')<p class="dash-error">{{ $message }}</p>@enderror
            </div>
            @endif
            @if ($hasPay)
            <div class="dash-field is-wide">
                <label for="stipend_amount">Stipend per release (₱)</label>
                <input id="stipend_amount" name="stipend_amount" type="number" min="0" max="1000000" step="0.01" inputmode="decimal" value="{{ old('stipend_amount', $s->stipend_amount) }}">
                <small>Filled in from the scholarship's published amount. Change it only for an exception. Leave empty if this scholarship has no stipend.</small>
                @error('stipend_amount')<p class="dash-error">{{ $message }}</p>@enderror
            </div>
            @endif
            <div class="is-wide">@include('provider.partials.section-foot', ['cancel' => $readUrl.'#scholarship', 'note' => $saveNote])</div>
        </form>
    </section>
    @else
    <section class="dash-card sp-card" id="scholarship" aria-labelledby="scholarship-title">
        <div class="sp-card-head">
            <h3 class="dash-card-title" id="scholarship-title"><i class="bi bi-award" aria-hidden="true"></i>Scholarship details</h3>
            @isset ($sections['scholarship'])
                <a class="dash-btn is-soft is-sm" href="{{ $editUrl('scholarship') }}"><i class="bi bi-pencil-square" aria-hidden="true"></i>Edit</a>
            @endisset
        </div>
        <dl class="sp-facts">
            <div><dt>Scholarship</dt><dd>{{ $s->scholarship->name }}</dd></div>
            @if ($hasTypes)<div><dt>Grant type</dt><dd>{!! $show($s->grantLabel()) !!}</dd></div>@endif
            @if ($hasPay)<div><dt>Stipend per release</dt><dd>{{ $s->stipend_amount !== null ? '₱'.number_format($s->stipend_amount, 2) : '—' }}</dd></div>@endif
            <div><dt>Scholar since</dt><dd>{{ $s->started_term ?: '—' }}</dd></div>
            <div><dt>Account</dt><dd>{{ [
                'none' => 'No BAKR account (added before accounts were created automatically)',
                'not_sent' => 'Created, but the login e-mail was not sent',
                'waiting' => 'Login e-mailed '.$s->credentials_sent_at?->format('M j, Y g:i A').'; not signed in yet',
                'active' => 'Active: the scholar has their own password',
            ][$s->accountState()] }}</dd></div>
        </dl>
        @if ($s->canResendLogin())
            <form method="POST" action="{{ route('provider.scholars.login', $s->id) }}" class="pv-resend sp-resend" data-busy="Sending login details…" data-busy-sub="A new temporary password is on its way">
                @csrf
                <button type="submit" class="dash-btn is-soft is-sm"><i class="bi bi-envelope-arrow-up" aria-hidden="true"></i>Resend login details</button>
                <small>Sends a new temporary password to {{ $s->contactEmail() }}. The old one stops working.@if (\App\Services\Provider\ScholarAccounts::mailIsTestMode()) <b class="pv-attn">E-mail is in test mode (MAIL_MAILER=log): nothing will be delivered.</b>@endif</small>
            </form>
        @endif
    </section>
    @endif

    {{-- ---------- Personal information ----------
         The name is editable (owner, 2026-11-23); birthday and gender are not once recorded (an older row that
         never had them can still get them, once). --}}
    @if ($editing === 'personal')
    <section class="dash-card sp-card is-editing" id="personal" aria-labelledby="personal-title">
        <form method="POST" action="{{ route('provider.scholars.update', $s->id) }}" class="dash-form-grid" novalidate>
            @csrf @method('PATCH')
            <input type="hidden" name="section" value="personal">
            <h3 class="dash-card-title is-wide" id="personal-title"><i class="bi bi-person" aria-hidden="true"></i>Personal information <span class="dash-pill is-warn ms-editing-pill"><i class="bi bi-pencil" aria-hidden="true"></i> Editing</span></h3>
            @php $nameNow = $s->nameParts(); @endphp
            @include('provider.partials.name-fields', ['values' => collect(\App\Support\PersonName::FIELDS)->mapWithKeys(fn ($f) => [$f => old($f, $nameNow[$f] ?? null)])->all()])
            @php $missingPersonal = array_values(array_filter([$s->birthdate === null ? 'birthdate' : null, filled($s->gender) ? null : 'gender'])); @endphp
            @if ($missingPersonal)
                @include('provider.partials.personal-fields', ['fields' => $missingPersonal, 'values' => [
                    'birthdate' => old('birthdate'), 'gender' => old('gender'),
                ]])
            @endif
            @if ($s->birthdate || filled($s->gender))
            <dl class="sp-facts is-wide sp-locked">
                @if ($s->birthdate)<div><dt>Birthday</dt><dd>{{ $s->birthdateLabel() }} <span class="sp-lock"><i class="bi bi-lock" aria-hidden="true"></i>Cannot be changed</span></dd></div>@endif
                @if (filled($s->gender))<div><dt>Gender</dt><dd>{{ $s->genderLabel() }} <span class="sp-lock"><i class="bi bi-lock" aria-hidden="true"></i>Cannot be changed</span></dd></div>@endif
            </dl>
            @endif
            <div class="is-wide">@include('provider.partials.section-foot', ['cancel' => $readUrl.'#personal', 'note' => 'Saves only this card. The name is also the scholar\'s account name.'])</div>
        </form>
    </section>
    @else
    <section class="dash-card sp-card" id="personal" aria-labelledby="personal-title">
        <div class="sp-card-head">
            <h3 class="dash-card-title" id="personal-title"><i class="bi bi-person" aria-hidden="true"></i>Personal information</h3>
            <a class="dash-btn is-soft is-sm" href="{{ $editUrl('personal') }}"><i class="bi bi-pencil-square" aria-hidden="true"></i>Edit</a>
        </div>
        <dl class="sp-facts">
            @php $nameNow = $s->nameParts(); @endphp
            <div><dt>First name</dt><dd>{!! $show($nameNow['first_name']) !!}</dd></div>
            <div><dt>Middle name</dt><dd>{{ $nameNow['middle_name'] ?: '—' }}</dd></div>
            <div><dt>Surname</dt><dd>{!! $show($nameNow['last_name']) !!}</dd></div>
            <div><dt>Suffix</dt><dd>{{ $nameNow['suffix'] ?: '—' }}</dd></div>
            <div><dt>Birthday</dt><dd>{!! $show($s->birthdateLabel()) !!}</dd></div>
            <div><dt>Gender</dt><dd>{!! $show($s->genderLabel()) !!}</dd></div>
        </dl>
    </section>
    @endif

    {{-- ---------- Contact information ----------
         Mobile + address (street, city / municipality, province) are edited here. The sign-in e-mail has its own
         form (?edit=email): it changes only after the scholar confirms the link sent to the new address. The mobile
         number is the way to reach the scholar when e-mail does not work (Call / Text). --}}
    @php
        $editingEmail = request('edit') === 'email' || $errors->has('new_email');
        $dial = $s->mobileDialString();
    @endphp
    @if ($editing === 'contact')
    <section class="dash-card sp-card is-editing" id="contact" aria-labelledby="contact-title">
        <form method="POST" action="{{ route('provider.scholars.update', $s->id) }}" class="dash-form-grid" novalidate>
            @csrf @method('PATCH')
            <input type="hidden" name="section" value="contact">
            <h3 class="dash-card-title is-wide" id="contact-title"><i class="bi bi-envelope" aria-hidden="true"></i>Contact information <span class="dash-pill is-warn ms-editing-pill"><i class="bi bi-pencil" aria-hidden="true"></i> Editing</span></h3>
            @include('provider.partials.personal-fields', ['fields' => ['mobile', 'address'], 'values' => [
                'mobile' => old('mobile', $s->mobileLabel()),
                'address_street' => old('address_street', $s->address_street ?? ($s->hasFullAddress() ? null : $s->address)),
                'address_city' => old('address_city', $s->address_city), 'address_province' => old('address_province', $s->address_province),
            ]])
            @if (! $s->hasFullAddress() && $s->address)
                <p class="pv-account-note is-wide"><i class="bi bi-info-circle" aria-hidden="true"></i><span>The address was recorded in one line before ("{{ $s->address }}"). Split it into street, city / municipality and province.</span></p>
            @endif
            <p class="pv-account-note is-wide"><i class="bi bi-envelope-check" aria-hidden="true"></i><span>The e-mail {{ $s->contactEmail() ?: '' }} is the scholar's sign-in. Change it with "Change e-mail" on this card.</span></p>
            <div class="is-wide">@include('provider.partials.section-foot', ['cancel' => $readUrl.'#contact', 'note' => $saveNote])</div>
        </form>
    </section>
    @else
    <section class="dash-card sp-card {{ $editingEmail ? 'is-editing' : '' }}" id="contact" aria-labelledby="contact-title">
        <div class="sp-card-head">
            <h3 class="dash-card-title" id="contact-title"><i class="bi bi-envelope" aria-hidden="true"></i>Contact information</h3>
            <a class="dash-btn is-soft is-sm" href="{{ $editUrl('contact') }}"><i class="bi bi-pencil-square" aria-hidden="true"></i>Edit</a>
        </div>
        <dl class="sp-facts">
            <div><dt>E-mail</dt><dd>{{ $s->contactEmail() ?: '—' }}</dd></div>
            <div><dt>Mobile number</dt><dd>{!! $show($s->mobileLabel()) !!}
                @if ($dial)
                    <span class="sp-dial">
                        <a class="dash-btn is-soft is-sm" href="tel:{{ $dial }}"><i class="bi bi-telephone" aria-hidden="true"></i>Call</a>
                        <a class="dash-btn is-soft is-sm" href="sms:{{ $dial }}"><i class="bi bi-chat-dots" aria-hidden="true"></i>Text</a>
                    </span>
                @endif
            </dd></div>
            <div><dt>Address</dt><dd>{!! $show($s->addressLabel()) !!}@if ($s->addressLabel() && ! $s->hasFullAddress()) <span class="pv-attn">Add street, city and province</span>@endif</dd></div>
        </dl>

        @if ($s->hasPendingEmail())
            <div class="sp-email-pending" role="status">
                <p><i class="bi bi-hourglass-split" aria-hidden="true"></i><span>Waiting for the scholar to confirm <strong>{{ $s->pending_email }}</strong> (link sent {{ $s->pending_email_sent_at->format('M j, Y g:i A') }}). Until then they sign in with {{ $s->contactEmail() }}.@if ($dial) Call or text them so they open it.@endif</span></p>
                <form method="POST" action="{{ route('provider.scholars.email.cancel', $s->id) }}">
                    @csrf @method('DELETE')
                    <button type="submit" class="dash-btn is-soft is-sm"><i class="bi bi-x-lg" aria-hidden="true"></i>Withdraw change</button>
                </form>
            </div>
        @endif

        @if ($editingEmail)
            <form method="POST" action="{{ route('provider.scholars.email', $s->id) }}" class="dash-form-grid sp-email-form" novalidate data-busy="Sending the confirmation link…" data-busy-sub="To the new e-mail address">
                @csrf
                <h4 class="is-wide sp-email-title"><i class="bi bi-envelope-at" aria-hidden="true"></i>Change sign-in e-mail <span class="dash-pill is-warn ms-editing-pill"><i class="bi bi-pencil" aria-hidden="true"></i> Editing</span></h4>
                <div class="dash-field is-wide">
                    <label for="new_email">New e-mail</label>
                    <input id="new_email" name="new_email" type="email" maxlength="190" required autocomplete="off" spellcheck="false" value="{{ old('new_email') }}" placeholder="name@gmail.com" aria-describedby="new_email-help">
                    <small id="new_email-help">We e-mail a confirmation link to the new address. The sign-in e-mail changes only when the scholar opens it (within {{ \App\Models\Scholar::EMAIL_CHANGE_HOURS }} hours), so you know the new address works. Until then they keep using {{ $s->contactEmail() }}.@if ($dial) Check the address with them by phone first.@endif</small>
                    @error('new_email')<p class="dash-error">{{ $message }}</p>@enderror
                    @if (\App\Services\Provider\ScholarAccounts::mailIsTestMode())<small class="pv-attn">E-mail is in test mode (MAIL_MAILER=log): nothing will be delivered.</small>@endif
                </div>
                <div class="is-wide">@include('provider.partials.section-foot', ['cancel' => $readUrl.'#contact', 'note' => 'Sends a confirmation link. Nothing changes until the scholar confirms.', 'saveLabel' => 'Send confirmation link'])</div>
            </form>
        @else
            <div class="pv-resend sp-resend">
                <a class="dash-btn is-soft is-sm" href="{{ route('provider.scholars.show', [$s->id, 'edit' => 'email']) }}#contact"><i class="bi bi-envelope-at" aria-hidden="true"></i>Change e-mail</a>
                <small>For a scholar who lost access to their e-mail. The new address must be confirmed by the scholar.</small>
            </div>
        @endif
    </section>
    @endif
</div>

{{-- ============ DOCUMENTS AND STIPENDS ============ --}}
@if ($hasReqs || $hasPay)
<div class="sp-grid">
    @if ($hasReqs)
    <section class="dash-card" aria-labelledby="reqs-title">
        <h3 class="dash-card-title" id="reqs-title"><i class="bi bi-folder-check" aria-hidden="true"></i>Maintaining documents by semester</h3>
        @forelse ($requirements as $termLabel => $items)
            <h4 class="pv-term">{{ $termLabel }} <small>{{ $items->filter->isOwed()->where('status', 'approved')->count() }}/{{ $items->filter->isOwed()->count() }} approved</small></h4>
            <ul class="pv-reqs">
                @foreach ($items as $r)
                    @include('provider.partials.requirement-item', ['r' => $r->setRelation('scholar', $s)])
                @endforeach
            </ul>
        @empty
            <p class="dash-empty">No maintaining documents yet. They appear once you set a semester's deadline for {{ $s->scholarship->shortName() }} on the Requirements page.</p>
        @endforelse
    </section>
    @endif

    @if ($hasPay)
    <section class="dash-card" aria-labelledby="pay-title">
        <h3 class="dash-card-title" id="pay-title"><i class="bi bi-cash-coin" aria-hidden="true"></i>Stipend history</h3>
        @if ($disbursements->isEmpty())
            <p class="dash-empty">No stipend records yet. Prepare a semester on the Disbursements page.</p>
        @else
            <div class="dash-table-wrap pv-flat">
                <table class="dash-table">
                    <thead><tr><th scope="col">Semester</th><th scope="col">Release</th><th scope="col" class="is-num">Amount</th><th scope="col">Status</th><th scope="col">Released</th><th scope="col">Reference</th></tr></thead>
                    <tbody>
                    @foreach ($disbursements as $d)
                        <tr>
                            <td>{{ $d->term }}</td>
                            <td>{{ $d->release_type === 'monthly' ? $d->periodLabel() : $d->releaseTypeLabel() }}</td>
                            <td class="is-num">₱{{ number_format($d->amount, 2) }}</td>
                            <td><span class="dash-status {{ $payPill[$d->status] ?? '' }}">{{ $d->statusLabel() }}</span></td>
                            <td>{{ $d->released_at?->format('M j, Y') ?? '—' }}</td>
                            <td>{{ $d->reference ?: '—' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
    @endif
</div>
@endif

@unless ($s->isTerminated())
{{-- Terminate: a dangerous action, so the same red confirmation as sign-out. --}}
<dialog class="dash-dialog dash-confirm" id="confirm-terminate" aria-labelledby="terminate-title">
    <div class="dash-dialog-body">
        <span class="dash-confirm-icon" aria-hidden="true"><i class="bi bi-x-circle"></i></span>
        <h2 id="terminate-title">Terminate {{ $s->displayName() }}?</h2>
        <p>Their {{ $s->scholarship->shortName() }} scholarship ends: no more stipends or requirement checklists. You can reinstate them later.</p>
    </div>
    <div class="dash-dialog-foot">
        <button type="button" class="dash-btn is-soft" data-close-dialog>Cancel</button>
        <form method="POST" action="{{ route('provider.scholars.status', $s->id) }}">
            @csrf
            @method('PATCH')
            <input type="hidden" name="status" value="terminated">
            <button type="submit" class="dash-btn is-danger"><i class="bi bi-x-circle" aria-hidden="true"></i>Terminate</button>
        </form>
    </div>
</dialog>
@endunless
@endsection
