@extends('provider.layout')

{{-- Add new, official scholars to BAKR: one at a time, or from an Excel file.
     The provider types only the scholar's own details (scholars table: scholar_code = Scholar ID, typed
     freely, full_name, student_number, email, campus, program, year_level, birthdate, gender (Male /
     Female), mobile, address; all required) and, for scholarships with more than one published type, the
     grant type (scholars.grant_type, e.g. CMSP Full / Half) and "Scholar since" (started_term, the semester
     they started; default the current one). Status (Active) and stipend (the type's published amount) are
     filled in by ScholarRoster.
     public/js/bakr-provider.js shows the grant choice and the automatic details of the scholarship
     picked; without JS the selected scholarship's are shown and the server still checks everything.
     Rows added here have no BAKR account and are visible only to this provider. --}}

@section('title', 'Add scholars')
@section('photo', 'opt/student.jpg')
@section('kicker', 'Scholars')
@section('heading')Add new <em>scholars</em>@endsection
@section('sub', 'Record each new official scholar so they have a place in BAKR. Add one person, or import your list from Excel.')
@section('actions')
    <a class="dash-btn is-soft" href="{{ route('provider.scholars') }}"><i class="bi bi-arrow-left" aria-hidden="true"></i>All scholars</a>
@endsection

@section('content')
@php
    $selectedId = (int) old('scholarship_id', $scholarships->first()?->id);
    $amount = fn ($g) => \App\Models\Scholarship::grantAmountLabel($g);
    $withTypes = $scholarships->filter(fn ($s) => $s->hasGrantTypes());
@endphp

@if ($scholarships->isEmpty())
    <div class="dash-card dash-empty"><i class="bi bi-inbox" aria-hidden="true"></i>No active scholarships are listed for {{ $provider['name'] }} yet, so there is nothing to add scholars to.</div>
@else

@if (\App\Services\Provider\ScholarAccounts::mailIsTestMode())
    <div class="dash-flash is-warn" role="status"><i class="bi bi-envelope-exclamation" aria-hidden="true"></i><span><strong>E-mail is in test mode.</strong> Login e-mails are only written to the server log (MAIL_MAILER=log), so new scholars will not receive their password. Set up e-mail (see docs/CONTACT-SETUP.md) before adding real scholars.</span></div>
@endif

@if ($report)
    <section class="dash-card pv-import-report" aria-labelledby="report-title">
        <h2 class="dash-card-title" id="report-title"><i class="bi bi-clipboard-check" aria-hidden="true"></i>Last import: {{ $report['added'] }} added to {{ $report['scholarship'] }}</h2>
        @if ($report['added'])
            <p class="pv-muted">{{ $report['new_accounts'] ?? 0 }} new {{ \Illuminate\Support\Str::plural('account', $report['new_accounts'] ?? 0) }} created. Login details were e-mailed to each new scholar. E-mails that already had a BAKR student account were turned into scholar accounts (same password) and notified.</p>
        @endif
        @if (! empty($report['not_emailed']))
            <p class="pv-muted"><b class="pv-attn">These login e-mails could not be sent.</b> Open each scholar and press "Resend login details".</p>
            <ul class="pv-import-skipped">
                @foreach ($report['not_emailed'] as $line)<li>{{ $line }}</li>@endforeach
            </ul>
        @endif
        @if ($report['skipped'])
            <p class="pv-muted">These rows were not added. Fix them in your file and import it again; rows already added are skipped automatically.</p>
            <ul class="pv-import-skipped">
                @foreach ($report['skipped'] as $line)<li>{{ $line }}</li>@endforeach
            </ul>
            @if ($report['more'])<p class="pv-muted">…and {{ $report['more'] }} more.</p>@endif
        @endif
        <a class="dash-btn is-sm" href="{{ route('provider.scholars') }}">See your scholars</a>
    </section>
@endif

<div class="dash-split pv-gap">
    {{-- One scholar --}}
    <section class="dash-card" aria-labelledby="one-title">
        <h2 class="dash-card-title" id="one-title"><i class="bi bi-person-plus" aria-hidden="true"></i>Add one scholar</h2>
        <form method="POST" action="{{ route('provider.scholars.store') }}" class="dash-form-grid" data-roster-form novalidate data-busy="Adding the scholar…" data-busy-sub="Creating their login and e-mailing it to them">
            @csrf
            <div class="dash-field is-wide">
                <label for="scholarship_id">Scholarship</label>
                <select id="scholarship_id" name="scholarship_id" required data-roster-scholarship>
                    @foreach ($scholarships as $s)
                        <option value="{{ $s->id }}" @selected($selectedId === $s->id)>{{ $s->shortName() }} — {{ $s->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Scholar ID: typed as your office writes it --}}
            <div class="dash-field is-wide">
                <label for="scholar_code">Scholar ID</label>
                <input id="scholar_code" name="scholar_code" class="pv-code-input" maxlength="{{ \App\Models\Scholar::SCHOLAR_CODE_MAX }}" required autocomplete="off" spellcheck="false"
                       value="{{ old('scholar_code') }}" aria-describedby="scholar_code-help">
                <small id="scholar_code-help">Type it exactly as your office writes it. Each scholar of a scholarship has their own Scholar ID.</small>
                @error('scholar_code')<p class="dash-error">{{ $message }}</p>@enderror
            </div>

            {{-- Grant type: only for scholarships with more than one published type --}}
            @foreach ($withTypes as $s)
                <fieldset class="dash-field is-wide pv-grant" data-for-scholarship="{{ $s->id }}" @if ($s->id !== $selectedId) hidden disabled @endif>
                    <legend>Grant type</legend>
                    <div class="pv-grant-options">
                        @foreach ($s->grantOptions() as $key => $g)
                            <label class="pv-grant-option">
                                <input type="radio" name="grant_type" value="{{ $key }}" required @checked(old('grant_type') === $key && $selectedId === $s->id)>
                                <span><strong>{{ $g['label'] }}</strong>@if ($withStipend && $amount($g))<small>{{ $amount($g) }}</small>@endif</span>
                            </label>
                        @endforeach
                    </div>
                    @error('grant_type')<p class="dash-error">{{ $message }}</p>@enderror
                </fieldset>
            @endforeach

            {{-- Personal information --}}
            <h3 class="pv-form-group is-wide"><i class="bi bi-person" aria-hidden="true"></i>Personal information</h3>
            <div class="dash-field is-wide">
                <label for="full_name">Full name</label>
                <input id="full_name" name="full_name" maxlength="150" required autocomplete="off" value="{{ old('full_name') }}" placeholder="e.g. Juan A. Dela Cruz">
                @error('full_name')<p class="dash-error">{{ $message }}</p>@enderror
            </div>
            @include('provider.partials.personal-fields', ['fields' => ['birthdate', 'gender'], 'values' => old()])

            {{-- Contact information --}}
            <h3 class="pv-form-group is-wide"><i class="bi bi-envelope" aria-hidden="true"></i>Contact information</h3>
            <div class="dash-field">
                <label for="email">E-mail (their sign-in)</label>
                <input id="email" name="email" type="email" maxlength="190" required autocomplete="off" value="{{ old('email') }}" aria-describedby="email-help" placeholder="name@example.com">
                @error('email')<p class="dash-error">{{ $message }}</p>@enderror
            </div>
            @include('provider.partials.personal-fields', ['fields' => ['mobile'], 'values' => old()])
            <p class="pv-account-note is-wide" id="email-help"><i class="bi bi-envelope-check" aria-hidden="true"></i><span>Adding the scholar creates their BAKR <strong>scholar account</strong> (they sign in at the Scholar login). A temporary password is e-mailed to this address, and they must change it the first time they sign in. If the address already has a BAKR student account, it becomes a scholar account instead (same password).</span></p>
            @include('provider.partials.personal-fields', ['fields' => ['address'], 'values' => old()])

            {{-- School information --}}
            <h3 class="pv-form-group is-wide"><i class="bi bi-building" aria-hidden="true"></i>School information</h3>
            <div class="dash-field">
                <label for="student_number">Student number</label>
                <input id="student_number" name="student_number" maxlength="30" required autocomplete="off" value="{{ old('student_number') }}" placeholder="e.g. 2026-00123">
                @error('student_number')<p class="dash-error">{{ $message }}</p>@enderror
            </div>
            <div class="dash-field">
                <label for="year_level">Year level</label>
                <select id="year_level" name="year_level" required>
                    <option value="">Choose…</option>
                    @foreach ($yearLevels as $key => $label)
                        <option value="{{ $key }}" @selected((string) old('year_level') === (string) $key)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('year_level')<p class="dash-error">{{ $message }}</p>@enderror
            </div>
            @include('provider.partials.campus-program', ['campus' => old('campus'), 'program' => old('program'), 'required' => true])

            {{-- Scholar since: their first semester (first checklist, first stipend) --}}
            <div class="dash-field is-wide">
                <label for="started_term">Scholar since</label>
                <select id="started_term" name="started_term" required aria-describedby="started_term-help">
                    @foreach ($startTerms as $t)
                        <option value="{{ $t }}" @selected(old('started_term', $term) === $t)>{{ $t }}</option>
                    @endforeach
                </select>
                <small id="started_term-help">The semester they started this scholarship. Maintaining documents and stipends start from this semester.</small>
                @error('started_term')<p class="dash-error">{{ $message }}</p>@enderror
            </div>

            {{-- Filled in automatically from the scholarship picked above --}}
            @foreach ($scholarships as $s)
                @php($options = $s->grantOptions())
                <div class="pv-auto is-wide" data-for-scholarship="{{ $s->id }}" @if ($s->id !== $selectedId) hidden @endif>
                    <p class="pv-auto-title"><i class="bi bi-check2-circle" aria-hidden="true"></i>Filled in for you</p>
                    <dl>
                        <div><dt>Status</dt><dd>Active</dd></div>
                        <div><dt>Account</dt><dd>BAKR scholar login, with a temporary password e-mailed to them</dd></div>
                        @if ($withStipend)
                            <div><dt>Stipend per release</dt><dd>
                                @if ($options === [])
                                    <span class="pv-muted">No fixed amount is published for {{ $s->shortName() }}. Set it on the scholar's profile if needed.</span>
                                @elseif (count($options) === 1)
                                    {{ $amount(reset($options)) }}
                                @else
                                    {{ collect($options)->map(fn ($g) => \Illuminate\Support\Str::before($g['label'], ' (').': '.$amount($g))->implode(' · ') }}
                                @endif
                            </dd></div>
                        @endif
                    </dl>
                </div>
            @endforeach

            <div class="is-wide"><button type="submit" class="dash-btn"><i class="bi bi-person-check" aria-hidden="true"></i>Add scholar</button></div>
        </form>
    </section>

    {{-- From Excel --}}
    <section class="dash-card" id="import" aria-labelledby="import-title">
        <h2 class="dash-card-title" id="import-title"><i class="bi bi-file-earmark-spreadsheet" aria-hidden="true"></i>Import from Excel</h2>
        <ol class="pv-steps">
            <li>Download the template, or use your own sheet with the same column titles in the first row.</li>
            <li>Put one new scholar per row.</li>
            <li>Save it as an Excel workbook (.xlsx) or CSV, then upload it here. Up to {{ number_format($maxRows) }} rows per file.</li>
        </ol>
        <a class="dash-btn is-soft is-sm" href="{{ route('provider.scholars.template') }}"><i class="bi bi-download" aria-hidden="true"></i>Download template (.xlsx)</a>

        <form method="POST" action="{{ route('provider.scholars.import') }}" enctype="multipart/form-data" class="dash-form-grid pv-mt" data-busy="Importing your scholars…" data-busy-sub="Creating each login and sending the e-mails. This can take a minute.">
            @csrf
            <div class="dash-field is-wide">
                <label for="import_scholarship">Add everyone in the file to</label>
                <select id="import_scholarship" name="scholarship_id" required>
                    @foreach ($scholarships as $s)
                        <option value="{{ $s->id }}" @selected($selectedId === $s->id)>{{ $s->shortName() }} — {{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="dash-field is-wide">
                <label for="import_started_term">Scholar since (everyone in the file)</label>
                <select id="import_started_term" name="started_term" required>
                    @foreach ($startTerms as $t)
                        <option value="{{ $t }}" @selected(old('started_term', $term) === $t)>{{ $t }}</option>
                    @endforeach
                </select>
                @error('started_term')<p class="dash-error">{{ $message }}</p>@enderror
            </div>
            <div class="dash-field is-wide">
                <label for="file">Excel or CSV file</label>
                <input id="file" name="file" type="file" required accept=".xlsx,.csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,text/csv">
                @error('file')<p class="dash-error">{{ $message }}</p>@enderror
                <small>Each scholar gets a BAKR account and a login e-mail, as above. Status and stipend are filled in the same way; "Scholar since" is the semester picked above. Import scholars who started in different semesters as separate files. Rows whose student number or e-mail is already on this scholarship are skipped, so importing the same file twice adds nobody twice. A Scholar ID that another scholar of the scholarship already has is reported.</small>
            </div>
            <div class="is-wide"><button type="submit" class="dash-btn"><i class="bi bi-upload" aria-hidden="true"></i>Import scholars</button></div>
        </form>

        <details class="pv-columns">
            <summary>Columns BAKR reads</summary>
            <ul>
                <li><b>Scholar ID</b>, <b>Full name</b>, <b>Student number</b>, <b>Email</b>, <b>Program</b>, <b>Year level</b> (all required). The Scholar ID is read exactly as typed. Year level as 1–4 or "2nd year". Program as its DNSC name, e.g. "Bachelor of Science in Information Technology" or "BS Information Technology". The e-mail becomes the scholar's sign-in.</li>
                <li><b>Mobile number</b>, <b>Address</b>, <b>Birthday</b>, <b>Gender</b> (all required). Mobile as 0917 123 4567 or +63 917 123 4567. Birthday as an Excel date, 2005-03-14 or 03/14/2005 (month first). Gender as Male or Female (M / F also work).</li>
                <li><b>Campus</b>: the DNSC campus the scholar studies at, e.g. "Main (Panabo Campus)", "Samal" or "Carmen". Needed whenever more than one campus offers the program (for example Information Technology); if only one campus offers it, you may leave it empty.</li>
                @if (in_array('Grant type', $columns, true))
                    <li><b>Grant type</b>: required for
                        @foreach ($withTypes as $s){{ $s->shortName() }} ({{ implode(' or ', array_map(fn ($g) => \Illuminate\Support\Str::before($g['label'], ' ('), $s->grantOptions())) }})@if (! $loop->last), @endif @endforeach.
                        Ignored for other scholarships.</li>
                @endif
            </ul>
            <p class="pv-muted">Other columns are ignored. "Name", "Student ID", "Course", "Contact number", "Date of birth" and "Sex" also work as titles.</p>
        </details>
    </section>
</div>

<p class="pv-privacy pv-gap"><i class="bi bi-shield-lock" aria-hidden="true"></i><span>Scholar records are visible only to {{ $provider['name'] }} and to the scholar. The temporary password goes only to the scholar's e-mail; BAKR never shows it to you or stores it in readable form.</span></p>
@endif
@endsection
