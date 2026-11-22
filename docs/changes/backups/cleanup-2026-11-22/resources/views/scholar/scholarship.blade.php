@extends('scholar.layout')
@section('title', 'My scholarship | BAKR')

{{-- Scholar portal › My scholarship: one scholarship at a time (tabs when the scholar holds several).
     Same layout as the provider's "My scholarships": a profile header, then collapsible sections
     (scholarship information, benefits, documents to keep it, stipends). Read-only. --}}

@php
    $s = $sc->scholarship;
    $payPill = ['released' => 'is-ok', 'pending' => 'is-warn'];
    $hasPay = $s->offers('disbursements');
    $hasReqs = $s->offers('requirements');
    $grant = $sc->grant();
    $released = $sc->disbursements->where('status', 'released');
@endphp

@section('hero_title')My <em>scholarship</em>@endsection
@section('hero_sub', 'What your scholarship gives you, what you need to keep it, and your stipends.')

@section('portal')
@if ($scholars->count() > 1)
    <nav class="dash-seg sc-tabs" aria-label="Choose a scholarship">
        @foreach ($scholars as $item)
            <a href="{{ route('scholar.scholarship', ['s' => $item->id]) }}" @if ($item->id === $sc->id) aria-current="true" @endif>{{ $item->scholarship->shortName() }}</a>
        @endforeach
    </nav>
@endif

{{-- ============ PROFILE HEADER ============ --}}
<section class="dash-card ms-hero" aria-labelledby="ms-title">
    <span class="ms-logo"><img src="{{ asset($s->logoPath()) }}" alt=""></span>
    <div class="ms-hero-copy">
        <span class="pv-sector pv-sector-{{ $s->sector }}">{{ $s->sectorLabel() }}</span>
        <h2 id="ms-title">{{ $s->name }}</h2>
        <p>{{ $s->provider }}</p>
        <div class="dash-meta ms-meta">
            <span class="dash-status {{ $sc->statusTone() }}">{{ $sc->statusLabel() }}</span>
            <span><i class="bi bi-calendar3" aria-hidden="true"></i>Scholar since {{ $sc->started_term ?: '—' }}</span>
            @if ($sc->grantLabel())<span><i class="bi bi-tag" aria-hidden="true"></i>{{ $sc->grantLabel() }}</span>@endif
        </div>
    </div>
    @if ($s->official_url)
        <div class="ms-hero-actions">
            <a class="dash-btn is-soft" href="{{ $s->official_url }}" target="_blank" rel="noopener noreferrer"><i class="bi bi-box-arrow-up-right" aria-hidden="true"></i>Official page</a>
        </div>
    @endif
</section>

<div class="ms-layout is-read">
    <div class="ms-main">

        {{-- ============ INFO ============ --}}
        <details class="dash-card ms-section ms-fold" id="info" open>
            <summary><h3 class="dash-card-title"><i class="bi bi-person-vcard" aria-hidden="true"></i>Scholarship information</h3></summary>
            <div class="ms-fold-body">
                @if ($s->overview)<p class="ms-lead">{{ $s->overview }}</p>@endif
                <dl class="sc-facts">
                    <div><dt>Scholarship</dt><dd>{{ $s->name }}</dd></div>
                    <div><dt>Provider</dt><dd>{{ $s->provider }}</dd></div>
                    <div><dt>Scholar ID</dt><dd>@if ($sc->scholar_code)<span class="pv-code">{{ $sc->scholar_code }}</span>@else Not recorded yet by your provider @endif</dd></div>
                    <div><dt>Status</dt><dd><span class="dash-status {{ $sc->statusTone() }}">{{ $sc->statusLabel() }}</span></dd></div>
                    @if ($s->hasGrantTypes())<div><dt>Grant type</dt><dd>{{ $sc->grantLabel() ?? 'Not recorded yet by your provider' }}</dd></div>@endif
                    <div><dt>Scholar since</dt><dd>{{ $sc->started_term ?: '—' }}</dd></div>
                    <div><dt>Student number</dt><dd>{{ $sc->student_number ?: '—' }}</dd></div>
                    <div><dt>Campus</dt><dd>{{ $sc->campusLabel() ?: '—' }}</dd></div>
                    <div><dt>Program</dt><dd>{{ $sc->program ?: '—' }}</dd></div>
                    <div><dt>Year level</dt><dd>{{ $sc->yearLabel() ?? '—' }}</dd></div>
                </dl>
            </div>
        </details>

        <div class="ms-two">
            {{-- ============ BENEFITS ============ --}}
            <details class="dash-card ms-section ms-fold" id="benefits">
                <summary><h3 class="dash-card-title"><i class="bi bi-gift" aria-hidden="true"></i>Benefits</h3></summary>
                <div class="ms-fold-body">
                    @if (empty($s->benefits))
                        <p class="pv-missing">{{ $s->provider }} has not published the benefits in BAKR. Ask them for the details.</p>
                    @else
                        <ul class="ms-list">@foreach ($s->benefits as $b)<li>{{ $b }}</li>@endforeach</ul>
                    @endif
                </div>
            </details>

            {{-- ============ MAINTAINING DOCUMENTS ============ --}}
            <details class="dash-card ms-section ms-fold" id="keep">
                <summary><h3 class="dash-card-title"><i class="bi bi-folder-check" aria-hidden="true"></i>Documents to keep your scholarship</h3></summary>
                <div class="ms-fold-body">
                    @if (! $hasReqs)
                        <p class="pv-office-note"><i class="bi bi-building" aria-hidden="true"></i><span>Submit your maintaining documents <strong>in person at the official DNSC scholarship office</strong> ({{ config('bakr.scholarship_office') }}). They are not uploaded in BAKR.</span></p>
                    @endif
                    @if (! $s->maintainingDocuments())
                        <p class="pv-missing">{{ $hasReqs ? $s->provider.' has not listed its maintaining documents in BAKR yet.' : 'Ask the '.config('bakr.scholarship_office').' what to submit each semester.' }}</p>
                    @else
                        <p class="ms-hint">{{ $s->shortName() }} asks for these every semester:</p>
                        <ul class="ms-list is-check">@foreach ($s->maintainingDocuments() as $d)<li>{{ $d['name'] }}@unless ($d['required']) <small>(only if it applies to you)</small>@endunless</li>@endforeach</ul>
                        @if ($hasReqs)
                            <a class="dash-btn is-sm" href="{{ route('scholar.requirements') }}#sch-{{ $sc->id }}"><i class="bi bi-upload" aria-hidden="true"></i>Submit on the Requirements page</a>
                        @endif
                    @endif
                </div>
            </details>
        </div>

        {{-- ============ DISBURSEMENT ============ --}}
        <details class="dash-card ms-section ms-fold" id="stipends">
            <summary><h3 class="dash-card-title"><i class="bi bi-cash-coin" aria-hidden="true"></i>Stipends</h3></summary>
            <div class="ms-fold-body">
                @if (! $hasPay)
                    <p class="pv-missing">{{ $s->provider }} handles stipends directly with you, not through BAKR. Contact them for release dates.</p>
                @else
                    <dl class="sc-facts">
                        <div><dt>Per release</dt><dd>{{ $sc->stipend_amount ? '₱'.number_format($sc->stipend_amount, 2) : 'Not set yet by your provider' }}</dd></div>
                        <div><dt>Released</dt><dd>{{ $grant ? 'Every '.($grant['per'] === 'month' ? 'month' : 'semester') : 'Per semester, or per month' }}</dd></div>
                        <div><dt>Received so far</dt><dd>₱{{ number_format($released->sum('amount'), 2) }} ({{ $released->count() }} {{ \Illuminate\Support\Str::plural('release', $released->count()) }})</dd></div>
                    </dl>
                    @if ($hasReqs)
                        <p class="ms-hint"><i class="bi bi-lock" aria-hidden="true"></i> {{ $s->provider }} releases a semester's stipend only after <b>all</b> your requirements for that semester are approved.</p>
                    @endif
                    @forelse ($sc->disbursements as $d)
                        @if ($loop->first)<ul class="dash-list">@endif
                        @php
                            // Why it can't be released yet, told to the scholar (the blocker texts are written for the provider).
                            $block = $d->status !== 'released' ? $sc->releaseBlock($d->term) : null;
                            $waiting = match ($block[0] ?? null) {
                                null => null,
                                'not_approved' => 'Stipend waiting for your requirements ('.$block[1].')',
                                'no_deadline', 'not_listed' => 'Waiting for '.$s->provider.' to open your '.$d->term.' maintaining documents',
                                'inactive' => 'On hold while your scholarship is marked inactive',
                                'terminated' => 'Not released: your scholarship was terminated',
                                default => 'Not for a semester before you became a scholar',
                            };
                        @endphp
                        <li>
                            <span class="dash-avatar {{ $d->status === 'released' ? 'is-1' : 'is-3' }}" aria-hidden="true"><i class="bi {{ $d->status === 'released' ? 'bi-check2' : ($waiting ? 'bi-lock' : 'bi-hourglass') }}"></i></span>
                            <div>
                                <strong>₱{{ number_format($d->amount, 2) }}</strong>
                                <span class="dash-meta">
                                    <span><i class="bi bi-calendar3" aria-hidden="true"></i>{{ $d->term }}@if ($d->release_type === 'monthly'), {{ $d->periodLabel() }}@endif</span>
                                    @if ($d->released_at)<span><i class="bi bi-check2" aria-hidden="true"></i>Released {{ $d->released_at->format('M j, Y') }}</span>@endif
                                    @if ($d->reference)<span><i class="bi bi-hash" aria-hidden="true"></i>Ref. {{ $d->reference }}</span>@endif
                                    @if ($waiting)<span><i class="bi bi-lock" aria-hidden="true"></i>{{ $waiting }}</span>@endif
                                </span>
                            </div>
                            <span class="dash-status {{ $payPill[$d->status] ?? '' }}">{{ $d->statusLabel() }}</span>
                        </li>
                        @if ($loop->last)</ul>@endif
                    @empty
                        <p class="dash-empty">No stipend records yet. They appear here when {{ $s->provider }} prepares a release.</p>
                    @endforelse
                @endif
            </div>
        </details>
    </div>
</div>
@endsection
