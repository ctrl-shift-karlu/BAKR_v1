@extends('provider.layout')

{{-- "My scholarships": read-only by default, in the same sections students see on the public
     Scholarships page. Every section has its OWN Edit button (?edit=<section>): only that section turns
     into a form and saving changes only its fields (a hidden "section" input tells the controller which).
     A failed save comes back with that section open and the typed values. Saving updates the public
     Scholarships page at once. Maintaining documents keep their own form (?documents=1). --}}

@section('title', 'My scholarships')
@section('photo', 'opt/dnsc_gate.jpg')
@section('kicker', 'My scholarships')
@section('heading')Your scholarship <em>information</em>@endsection
@section('sub', 'What students see about your scholarships. Press Edit on a section to change just that section; saving updates the public Scholarships page right away.')
@section('actions')
    <a class="dash-btn is-soft" href="{{ url('scholarships') }}" target="_blank" rel="noopener"><i class="bi bi-box-arrow-up-right" aria-hidden="true"></i>View public page</a>
@endsection

@section('content')
@php
    $typeHint = [
        'eq' => 'Checked from the student\'s answers', 'in' => 'Checked from the student\'s answers', 'not_in' => 'Checked from the student\'s answers',
        'contains_any' => 'Checked from the student\'s answers', 'contains_none' => 'Checked from the student\'s answers', 'maybe_in' => 'Checked from the student\'s answers',
        'program_in' => 'Checked against the student\'s exact DNSC program',
        'gte' => 'Minimum value', 'lte' => 'Maximum value', 'income_lte' => 'Maximum family income (₱)', 'any_of' => 'Passes when ANY option below passes',
        'confirm' => 'BAKR cannot check this; students confirm it with you',
    ];
    $lines = fn ($v) => implode("\n", $v ?? []);
    // The maintaining documents have their own form (?documents=1); every other section is edited on its own (?edit=<section>).
    $docErrors = collect($errors->keys())->contains(fn ($k) => str_starts_with($k, 'docs'));
    $editingDocs = $scope->has('requirements') && (request()->boolean('documents') || $docErrors);
    $sectionKeys = ['overview', 'qualifications', 'benefits', 'documents', 'period', 'apply'];
    $asked = request('edit', old('section'));
    $editSection = (! $editingDocs && in_array($asked, $sectionKeys, true)) ? $asked : null;
@endphp

@if ($scholarships->isEmpty())
    <div class="dash-card dash-empty"><i class="bi bi-inbox" aria-hidden="true"></i>No active scholarships are listed for {{ $provider['name'] }}.</div>
@else
@if ($scholarships->count() > 1)
    <nav class="dash-seg pv-first" aria-label="Choose a scholarship">
        @foreach ($scholarships as $s)
            <a href="{{ route('provider.scholarships', ['s' => $s->id]) }}" @if ($s->id === $selected) aria-current="true" @endif>{{ $s->shortName() }}</a>
        @endforeach
    </nav>
@endif

@foreach ($scholarships as $s)
    @continue($s->id !== $selected)
    @php
        $readUrl = route('provider.scholarships', ['s' => $s->id]);
        $editUrl = fn (string $key) => route('provider.scholarships', ['s' => $s->id, 'edit' => $key]).'#'.$key;
        $dateOr = fn ($d) => $d ? $d->format('M j, Y') : null;
    @endphp

    {{-- ============ PROFILE HEADER ============ --}}
    <section class="dash-card ms-hero" aria-labelledby="ms-title">
        <span class="ms-logo"><img src="{{ asset($s->logoPath()) }}" alt=""></span>
        <div class="ms-hero-copy">
            <span class="pv-sector pv-sector-{{ $s->sector }}">{{ $s->sectorLabel() }}</span>
            <h2 id="ms-title">{{ $s->name }}</h2>
            <p>{{ $s->provider }}</p>
            <div class="ms-chips">
                @include('partials.call-status', ['scholarship' => $s, 'compact' => true])
                @if ($s->verified_on)
                    <span class="ms-chip"><i class="bi bi-patch-check" aria-hidden="true"></i>Rules last checked {{ $s->verified_on->format('M j, Y') }}</span>
                @endif
            </div>
        </div>
        {{-- The scholarship's own official page (How to apply › Official page), else the provider's official site (owner, 2026-11-21). --}}
        @if ($s->officialWebsite())
            <div class="ms-hero-actions">
                <a class="dash-btn is-soft" href="{{ $s->officialWebsite() }}" target="_blank" rel="noopener noreferrer" title="Opens the official website of {{ $s->name }} in a new tab"><i class="bi bi-box-arrow-up-right" aria-hidden="true"></i>Official website<span class="visually-hidden"> (opens in a new tab)</span></a>
            </div>
        @endif
    </section>

    @if ($errors->any() && $editSection)
        <div class="dash-flash is-error" role="alert"><i class="bi bi-exclamation-triangle" aria-hidden="true"></i><span>Some fields need your attention. Nothing was saved yet.</span></div>
    @endif

    <div class="ms-layout is-read">
        <div class="ms-main">

            {{-- ---------- Overview ---------- --}}
            @if ($editSection === 'overview')
            <section class="dash-card ms-section is-editing" id="overview">
                <form method="POST" action="{{ route('provider.scholarships.update', $s->id) }}">
                    @csrf @method('PUT')
                    <input type="hidden" name="section" value="overview">
                    <h3 class="dash-card-title"><i class="bi bi-info-circle" aria-hidden="true"></i>Overview <span class="dash-pill is-warn ms-editing-pill"><i class="bi bi-pencil" aria-hidden="true"></i> Editing</span></h3>
                    <div class="dash-field">
                        <label for="field-overview" class="visually-hidden">Overview</label>
                        <textarea id="field-overview" name="overview" rows="6" maxlength="2000">{{ old('overview', $s->overview) }}</textarea>
                        @error('overview')<p class="dash-error">{{ $message }}</p>@enderror
                    </div>
                    @include('provider.partials.section-foot', ['cancel' => $readUrl.'#overview'])
                </form>
            </section>
            @else
            <details class="dash-card ms-section ms-fold" id="overview">
                <summary><h3 class="dash-card-title"><i class="bi bi-info-circle" aria-hidden="true"></i>Overview</h3><a class="dash-btn is-soft is-sm ms-edit" href="{{ $editUrl('overview') }}"><i class="bi bi-pencil-square" aria-hidden="true"></i>Edit</a></summary>
                <div class="ms-fold-body">
                    <p class="ms-lead {{ filled($s->overview) ? '' : 'pv-missing' }}">{{ filled($s->overview) ? $s->overview : $missing }}</p>
                </div>
            </details>
            @endif

            {{-- ---------- Qualifications ---------- --}}
            @if ($editSection === 'qualifications')
            <section class="dash-card ms-section is-editing" id="qualifications">
                <form method="POST" action="{{ route('provider.scholarships.update', $s->id) }}">
                    @csrf @method('PUT')
                    <input type="hidden" name="section" value="qualifications">
                    <h3 class="dash-card-title"><i class="bi bi-list-check" aria-hidden="true"></i>Qualifications <span class="dash-pill is-warn ms-editing-pill"><i class="bi bi-pencil" aria-hidden="true"></i> Editing</span></h3>
                    <div class="dash-flash is-warn"><i class="bi bi-exclamation-triangle" aria-hidden="true"></i>
                        <span>These rules decide who BAKR shows as a match. Changing a description, a number or “Required” re-checks every student when you save. Only publish numbers you have officially announced.</span>
                    </div>
                    <ol class="pv-rules">
                        @foreach ($s->rules ?? [] as $i => $rule)
                            @php($type = $rule['type'] ?? '')
                            <li class="pv-rule">
                                <div class="pv-rule-head">
                                    <span class="dash-pill {{ $type === 'confirm' ? 'is-warn' : 'is-info' }}">{{ $typeHint[$type] ?? 'Checked from the student\'s answers' }}</span>
                                    <label class="dash-check">
                                        <input type="hidden" name="rules[{{ $i }}][required]" value="0">
                                        <input type="checkbox" name="rules[{{ $i }}][required]" value="1" @checked(old("rules.$i.required", $rule['required'] ?? false))> Required
                                    </label>
                                </div>
                                <div class="dash-field">
                                    <label for="rule-{{ $i }}-label">Description</label>
                                    <input id="rule-{{ $i }}-label" name="rules[{{ $i }}][label]" maxlength="255" required value="{{ old("rules.$i.label", $rule['label'] ?? '') }}">
                                    @error("rules.$i.label")<p class="dash-error">{{ $message }}</p>@enderror
                                </div>
                                @if (in_array($type, \App\Services\Provider\ScholarshipEditor::NUMERIC_TYPES, true))
                                    <div class="dash-field pv-num">
                                        <label for="rule-{{ $i }}-value">{{ $type === 'income_lte' ? 'Income cap (₱)' : (str_contains($rule['fact'] ?? '', 'percent') ? 'Grade (%)' : 'Value') }}</label>
                                        <input id="rule-{{ $i }}-value" name="rules[{{ $i }}][value]" type="number" step="{{ $type === 'income_lte' ? '1' : '0.01' }}" min="0" max="{{ $type === 'income_lte' ? 100000000 : (str_contains($rule['fact'] ?? '', 'percent') ? 100 : 1000000) }}" required value="{{ old("rules.$i.value", $rule['value'] ?? '') }}">
                                        @error("rules.$i.value")<p class="dash-error">{{ $message }}</p>@enderror
                                    </div>
                                @endif
                                @if ($type === 'confirm')
                                    <div class="dash-field">
                                        <label for="rule-{{ $i }}-note">Note shown to students</label>
                                        <textarea id="rule-{{ $i }}-note" name="rules[{{ $i }}][confirm_note]" rows="2" maxlength="500">{{ old("rules.$i.confirm_note", $rule['confirm_note'] ?? '') }}</textarea>
                                    </div>
                                @endif
                                @if ($type === 'any_of')
                                    <div class="pv-anyof-edit">
                                        @foreach ($rule['rules'] ?? [] as $j => $sub)
                                            <div class="dash-form-grid">
                                                <div class="dash-field">
                                                    <label for="rule-{{ $i }}-{{ $j }}-label">Option {{ $j + 1 }}</label>
                                                    <input id="rule-{{ $i }}-{{ $j }}-label" name="rules[{{ $i }}][sub][{{ $j }}][label]" maxlength="255" required value="{{ old("rules.$i.sub.$j.label", $sub['label'] ?? '') }}">
                                                </div>
                                                @if (in_array($sub['type'] ?? '', \App\Services\Provider\ScholarshipEditor::NUMERIC_TYPES, true))
                                                    <div class="dash-field">
                                                        <label for="rule-{{ $i }}-{{ $j }}-value">{{ str_contains($sub['fact'] ?? '', 'percent') ? 'Grade (%)' : 'Value' }}</label>
                                                        <input id="rule-{{ $i }}-{{ $j }}-value" name="rules[{{ $i }}][sub][{{ $j }}][value]" type="number" step="0.01" min="0" max="{{ str_contains($sub['fact'] ?? '', 'percent') ? 100 : 1000000 }}" required value="{{ old("rules.$i.sub.$j.value", $sub['value'] ?? '') }}">
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </li>
                        @endforeach
                    </ol>
                    @include('provider.partials.section-foot', ['cancel' => $readUrl.'#qualifications'])
                </form>
            </section>
            @else
            <details class="dash-card ms-section ms-fold" id="qualifications">
                <summary><h3 class="dash-card-title"><i class="bi bi-list-check" aria-hidden="true"></i>Qualifications</h3><a class="dash-btn is-soft is-sm ms-edit" href="{{ $editUrl('qualifications') }}"><i class="bi bi-pencil-square" aria-hidden="true"></i>Edit</a></summary>
                <div class="ms-fold-body">
                    <p class="ms-hint">BAKR checks these against each student's answers. "Confirm with provider" items are ones students settle with you.</p>
                    @include('partials.scholarship-qualifications', ['scholarship' => $s, 'missing' => $missing])
                </div>
            </details>
            @endif

            <div class="ms-two">
                {{-- ---------- Benefits ---------- --}}
                @if ($editSection === 'benefits')
                <section class="dash-card ms-section is-editing" id="benefits">
                    <form method="POST" action="{{ route('provider.scholarships.update', $s->id) }}">
                        @csrf @method('PUT')
                        <input type="hidden" name="section" value="benefits">
                        <h3 class="dash-card-title"><i class="bi bi-gift" aria-hidden="true"></i>Benefits <span class="dash-pill is-warn ms-editing-pill"><i class="bi bi-pencil" aria-hidden="true"></i> Editing</span></h3>
                        <div class="dash-field">
                            <label for="field-benefits" class="visually-hidden">Benefits</label>
                            <textarea id="field-benefits" name="benefits" rows="7">{{ old('benefits', $lines($s->benefits)) }}</textarea>
                            <small>One benefit per line.</small>
                            @error('benefits')<p class="dash-error">{{ $message }}</p>@enderror
                        </div>
                        @include('provider.partials.section-foot', ['cancel' => $readUrl.'#benefits'])
                    </form>
                </section>
                @else
                <details class="dash-card ms-section ms-fold" id="benefits">
                    <summary><h3 class="dash-card-title"><i class="bi bi-gift" aria-hidden="true"></i>Benefits</h3><a class="dash-btn is-soft is-sm ms-edit" href="{{ $editUrl('benefits') }}"><i class="bi bi-pencil-square" aria-hidden="true"></i>Edit</a></summary>
                    <div class="ms-fold-body">
                        @if (empty($s->benefits))<p class="pv-missing">{{ $missing }}</p>@else<ul class="ms-list">@foreach ($s->benefits as $b)<li>{{ $b }}</li>@endforeach</ul>@endif
                    </div>
                </details>
                @endif

                {{-- ---------- Documents to apply ---------- --}}
                @if ($editSection === 'documents')
                <section class="dash-card ms-section is-editing" id="documents">
                    <form method="POST" action="{{ route('provider.scholarships.update', $s->id) }}">
                        @csrf @method('PUT')
                        <input type="hidden" name="section" value="documents">
                        <h3 class="dash-card-title"><i class="bi bi-file-earmark-text" aria-hidden="true"></i>Documents to apply <span class="dash-pill is-warn ms-editing-pill"><i class="bi bi-pencil" aria-hidden="true"></i> Editing</span></h3>
                        <div class="dash-field">
                            <label for="field-documents" class="visually-hidden">Documents to apply</label>
                            <textarea id="field-documents" name="documents" rows="7">{{ old('documents', $lines($s->documents)) }}</textarea>
                            <small>One document per line.</small>
                            @error('documents')<p class="dash-error">{{ $message }}</p>@enderror
                        </div>
                        @include('provider.partials.section-foot', ['cancel' => $readUrl.'#documents'])
                    </form>
                </section>
                @else
                <details class="dash-card ms-section ms-fold" id="documents">
                    <summary><h3 class="dash-card-title"><i class="bi bi-file-earmark-text" aria-hidden="true"></i>Documents to apply</h3><a class="dash-btn is-soft is-sm ms-edit" href="{{ $editUrl('documents') }}"><i class="bi bi-pencil-square" aria-hidden="true"></i>Edit</a></summary>
                    <div class="ms-fold-body">
                        @if (empty($s->documents))<p class="pv-missing">{{ $missing }}</p>@else<ul class="ms-list is-check">@foreach ($s->documents as $d)<li>{{ $d }}</li>@endforeach</ul>@endif
                    </div>
                </details>
                @endif
            </div>

            @if ($scope->has('requirements'))
                @include('provider.partials.maintaining-docs', ['s' => $s, 'editing' => $editingDocs])
            @else
                <details class="dash-card ms-section ms-fold" id="maintaining">
                    <summary><h3 class="dash-card-title"><i class="bi bi-folder-check" aria-hidden="true"></i>Maintaining documents</h3></summary>
                    <div class="ms-fold-body">
                        <p class="pv-office-note"><i class="bi bi-building" aria-hidden="true"></i><span>Your scholars submit their maintaining documents <strong>in person at the official DNSC scholarship office</strong> ({{ config('bakr.scholarship_office') }}), not through BAKR. When a scholar's documents are not complete, open them under Scholars and press <strong>Mark inactive</strong>.</span></p>
                    </div>
                </details>
            @endif
        </div>

        <aside class="ms-side">
            {{-- ---------- Application period ---------- --}}
            @if ($editSection === 'period')
            <section class="dash-card ms-section is-editing" id="period">
                <form method="POST" action="{{ route('provider.scholarships.update', $s->id) }}">
                    @csrf @method('PUT')
                    <input type="hidden" name="section" value="period">
                    <h3 class="dash-card-title"><i class="bi bi-calendar-event" aria-hidden="true"></i>Application period <span class="dash-pill is-warn ms-editing-pill"><i class="bi bi-pencil" aria-hidden="true"></i> Editing</span></h3>
                    <div class="dash-field">
                        <label for="application_opens_on">Applications open</label>
                        <input id="application_opens_on" name="application_opens_on" type="date" value="{{ old('application_opens_on', $s->application_opens_on?->format('Y-m-d')) }}">
                        @error('application_opens_on')<p class="dash-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="dash-field">
                        <label for="application_closes_on">Applications close (deadline)</label>
                        <input id="application_closes_on" name="application_closes_on" type="date" value="{{ old('application_closes_on', $s->application_closes_on?->format('Y-m-d')) }}">
                        @error('application_closes_on')<p class="dash-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="dash-field">
                        <label for="results_on">Results released</label>
                        <input id="results_on" name="results_on" type="date" value="{{ old('results_on', $s->results_on?->format('Y-m-d')) }}">
                        @error('results_on')<p class="dash-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="dash-field">
                        <label for="window_note">Notes</label>
                        <textarea id="window_note" name="window_note" rows="3" maxlength="500" placeholder="e.g. Interviews July 25 – August 18">{{ old('window_note', $s->window_note) }}</textarea>
                        <small>Leave a date empty if it is not announced. Students then see no deadline instead of a wrong one.</small>
                        @error('window_note')<p class="dash-error">{{ $message }}</p>@enderror
                    </div>
                    @include('provider.partials.section-foot', ['cancel' => $readUrl.'#period'])
                </form>
            </section>
            @else
            <details class="dash-card ms-section ms-fold" id="period">
                <summary><h3 class="dash-card-title"><i class="bi bi-calendar-event" aria-hidden="true"></i>Application period</h3><a class="dash-btn is-soft is-sm ms-edit" href="{{ $editUrl('period') }}"><i class="bi bi-pencil-square" aria-hidden="true"></i>Edit</a></summary>
                <div class="ms-fold-body">
                    @include('partials.call-status', ['scholarship' => $s])
                    <dl class="ms-dates">
                        <div><dt>Applications open</dt><dd class="{{ $s->application_opens_on ? '' : 'is-empty' }}">{{ $dateOr($s->application_opens_on) ?? 'Not announced' }}</dd></div>
                        <div><dt>Deadline</dt><dd class="{{ $s->application_closes_on ? '' : 'is-empty' }}">{{ $dateOr($s->application_closes_on) ?? 'Not announced' }}</dd></div>
                        <div><dt>Results</dt><dd class="{{ $s->results_on ? '' : 'is-empty' }}">{{ $dateOr($s->results_on) ?? 'Not announced' }}</dd></div>
                    </dl>
                    @if (filled($s->window_note))
                        <p class="ms-note">{{ $s->window_note }}</p>
                    @elseif (! $s->callStatus())
                        <p class="ms-note pv-missing">{{ $missing }}</p>
                    @endif
                </div>
            </details>
            @endif

            {{-- ---------- How to apply ---------- --}}
            @if ($editSection === 'apply')
            <section class="dash-card ms-section is-editing" id="apply">
                <form method="POST" action="{{ route('provider.scholarships.update', $s->id) }}">
                    @csrf @method('PUT')
                    <input type="hidden" name="section" value="apply">
                    <h3 class="dash-card-title"><i class="bi bi-send" aria-hidden="true"></i>How to apply <span class="dash-pill is-warn ms-editing-pill"><i class="bi bi-pencil" aria-hidden="true"></i> Editing</span></h3>
                    <div class="dash-field">
                        <label for="application_route" class="visually-hidden">How to apply</label>
                        <textarea id="application_route" name="application_route" rows="5" maxlength="500">{{ old('application_route', $s->application_route) }}</textarea>
                        @error('application_route')<p class="dash-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="dash-field">
                        <label for="official_url">Official page</label>
                        <input id="official_url" name="official_url" type="url" maxlength="255" value="{{ old('official_url', $s->official_url) }}" placeholder="https://">
                        @error('official_url')<p class="dash-error">{{ $message }}</p>@enderror
                    </div>
                    @include('provider.partials.section-foot', ['cancel' => $readUrl.'#apply'])
                </form>
            </section>
            @else
            <details class="dash-card ms-section ms-fold" id="apply">
                <summary><h3 class="dash-card-title"><i class="bi bi-send" aria-hidden="true"></i>How to apply</h3><a class="dash-btn is-soft is-sm ms-edit" href="{{ $editUrl('apply') }}"><i class="bi bi-pencil-square" aria-hidden="true"></i>Edit</a></summary>
                <div class="ms-fold-body">
                    <p class="{{ filled($s->application_route) ? '' : 'pv-missing' }}">{{ filled($s->application_route) ? $s->application_route : $missing }}</p>
                    @if ($s->official_url)
                        <a class="ms-link" href="{{ $s->official_url }}" target="_blank" rel="noopener noreferrer"><i class="bi bi-box-arrow-up-right" aria-hidden="true"></i>Official page</a>
                    @endif
                </div>
            </details>
            @endif

            <details class="dash-card ms-section ms-fold ms-source">
                <summary><h3 class="dash-card-title"><i class="bi bi-journal-bookmark" aria-hidden="true"></i>Source</h3></summary>
                <div class="ms-fold-body">
                    <p class="{{ filled($s->source_note) ? '' : 'pv-missing' }}">{{ filled($s->source_note) ? $s->source_note : $missing }}</p>
                </div>
            </details>

            <details class="dash-card ms-section ms-fold">
                <summary><h3 class="dash-card-title"><i class="bi bi-clock-history" aria-hidden="true"></i>Recent changes</h3></summary>
                <div class="ms-fold-body">
                    @forelse ($revisions as $rev)
                        @if ($loop->first)<ul class="dash-list">@endif
                        <li>
                            <span class="dash-avatar {{ $rev->rules_changed ? 'is-4' : 'is-1' }}" aria-hidden="true"><i class="bi {{ $rev->rules_changed ? 'bi-sliders' : 'bi-pencil' }}"></i></span>
                            <div>
                                <strong>{{ $rev->scholarship->shortName() }} · {{ $rev->rules_changed ? 'Qualifications and text' : 'Text' }}</strong>
                                <small>{{ $rev->user?->name ?? 'BAKR (official source update)' }} · {{ $rev->created_at->format('M j, Y g:i A') }}</small>
                            </div>
                        </li>
                        @if ($loop->last)</ul>@endif
                    @empty
                        <p class="dash-empty">No edits yet.</p>
                    @endforelse
                </div>
            </details>
        </aside>
    </div>
@endforeach
@endif
@endsection
