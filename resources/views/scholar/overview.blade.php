@extends('scholar.layout')
@section('title', 'Overview | BAKR')

{{-- Scholar portal › Overview: a summary of every scholarship held and the things that need doing now.
     A scholar account with no scholarship linked (e.g. removed by the provider) gets an empty state. --}}

@php
    $hour = (int) now()->format('G');
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
    $first = auth()->user()->firstName();
    $anyReqs = $scholars->contains(fn ($sc) => $sc->scholarship->offers('requirements'));
    $anyPay = $scholars->contains(fn ($sc) => $sc->scholarship->offers('disbursements'));
    $released = $scholars->flatMap->disbursements->where('status', 'released')->sortByDesc('released_at')->first();
    $pending = $scholars->flatMap->disbursements->where('status', 'pending');
@endphp

@section('hero_title'){{ $greeting }}, <em>{{ $first }}</em>@endsection
@section('hero_sub', $term.' · what needs your attention.')
@section('hero_actions')
    <a class="dash-btn is-sun" href="{{ route('scholar.requirements') }}"><i class="bi bi-folder-check" aria-hidden="true"></i>Requirements</a>
    <a class="dash-btn is-soft" href="{{ route('scholar.scholarship') }}"><i class="bi bi-award" aria-hidden="true"></i>My scholarship</a>
@endsection

@section('portal')
{{-- ============ SUMMARY ============ --}}
<section class="dash-kpis" aria-label="Summary">
    <a class="dash-kpi is-1" href="{{ route('scholar.scholarship') }}">
        <span class="dash-kpi-icon"><i class="bi bi-award" aria-hidden="true"></i></span>
        <span><strong>{{ $scholars->count() }}</strong><span>{{ \Illuminate\Support\Str::plural('Scholarship', $scholars->count()) }} held</span></span>
    </a>
    @if ($anyReqs)
        <a class="dash-kpi is-2" href="{{ route('scholar.requirements') }}">
            <span class="dash-kpi-icon"><i class="bi bi-check2-circle" aria-hidden="true"></i></span>
            <span><strong>{{ $reqs['approved'] }}/{{ $reqs['total'] }}</strong><span>Documents approved</span></span>
        </a>
        <a class="dash-kpi is-3" href="{{ route('scholar.requirements') }}">
            <span class="dash-kpi-icon"><i class="bi bi-hourglass-split" aria-hidden="true"></i></span>
            <span><strong>{{ $reqs['submitted'] }}</strong><span>Waiting for review</span></span>
        </a>
    @endif
    @if ($anyPay)
        <a class="dash-kpi is-4" href="{{ route('scholar.scholarship') }}#stipends">
            <span class="dash-kpi-icon"><i class="bi bi-cash-coin" aria-hidden="true"></i></span>
            <span><strong>{{ $released ? '₱'.number_format($released->amount, 0) : '—' }}</strong><span>{{ $released ? 'Last stipend, '.$released->released_at?->format('M j') : 'No stipend yet' }}</span></span>
        </a>
    @endif
</section>

<div class="dash-split pv-gap">
    {{-- ============ ACTIONS NEEDED ============ --}}
    <section class="dash-card" aria-labelledby="todo-title">
        <h2 class="dash-card-title" id="todo-title"><i class="bi bi-list-task" aria-hidden="true"></i>Actions needed</h2>
        @forelse ($actions as [$tone, $icon, $title, $detail, $url])
            @if ($loop->first)<ul class="sc-actions">@endif
            <li class="is-{{ $tone }}">
                <span class="sc-action-icon" aria-hidden="true"><i class="bi {{ $icon }}"></i></span>
                <div><strong>{{ $title }}</strong><small>{{ $detail }}</small></div>
                <a class="dash-btn is-soft is-sm" href="{{ $url }}">Open</a>
            </li>
            @if ($loop->last)</ul>@endif
        @empty
            <p class="dash-empty"><i class="bi bi-check2-all" aria-hidden="true"></i>You're all set. Nothing needs your attention right now.</p>
        @endforelse
        @if ($pending->isNotEmpty())
            <p class="sc-hint"><i class="bi bi-info-circle" aria-hidden="true"></i> {{ $pending->count() }} {{ \Illuminate\Support\Str::plural('stipend', $pending->count()) }} not released yet. Each is released once that semester's requirements are all approved.</p>
        @endif
    </section>

    {{-- ============ MY SCHOLARSHIPS ============ --}}
    <section class="dash-card" aria-labelledby="mine-title">
        <h2 class="dash-card-title" id="mine-title"><i class="bi bi-mortarboard" aria-hidden="true"></i>Your {{ \Illuminate\Support\Str::plural('scholarship', $scholars->count()) }}</h2>
        @if ($scholars->isEmpty())
            <p class="dash-empty">No scholarship is linked to your account yet. Contact your provider or use <a href="{{ route('scholar.help') }}#message">Help &amp; contact</a>.</p>
        @endif
        <ul class="dash-list">
            @foreach ($scholars as $sc)
                <li>
                    <span class="dash-avatar is-{{ ($loop->index % 4) + 1 }}" aria-hidden="true">{{ $sc->scholarship->shortName() }}</span>
                    <div>
                        <strong>{{ $sc->scholarship->name }}</strong>
                        <span class="dash-meta">
                            <span><i class="bi bi-building" aria-hidden="true"></i>{{ $sc->scholarship->provider }}</span>
                            @if ($sc->grantLabel())<span><i class="bi bi-tag" aria-hidden="true"></i>{{ $sc->grantLabel() }}</span>@endif
                            @if ($sc->scholarship->offers('disbursements') && $sc->stipend_amount)<span><i class="bi bi-cash-coin" aria-hidden="true"></i>₱{{ number_format($sc->stipend_amount, 0) }} per {{ $sc->grant()['per'] ?? 'release' }}</span>@endif
                        </span>
                    </div>
                    <span class="dash-status {{ $sc->statusTone() }}">{{ $sc->statusLabel() }}</span>
                </li>
            @endforeach
        </ul>
        <a class="dash-btn is-sm pv-gap" href="{{ route('scholar.scholarship') }}">See details</a>
    </section>
</div>

{{-- ============ UPDATES FROM YOUR PROVIDER (2026-11-21) ============
     Status changes, corrections made and replies (App\Notifications\ScholarUpdate). Same rows as "Actions needed";
     opening this page marks them read. --}}
@if ($updates->isNotEmpty())
<section class="dash-card pv-gap" id="updates" aria-labelledby="updates-title">
    <h2 class="dash-card-title" id="updates-title"><i class="bi bi-bell" aria-hidden="true"></i>Updates from your provider</h2>
    <ul class="sc-actions">
        @foreach ($updates as $n)
            @php
                $d = $n->data;
                [$tone, $icon] = match ($d['kind'] ?? '') {
                    'status' => str_contains($d['title'] ?? '', 'terminated') ? ['danger', 'bi-x-circle'] : (str_contains($d['title'] ?? '', 'inactive') ? ['warn', 'bi-pause-circle'] : ['info', 'bi-check2-circle']),
                    'correction' => ['info', 'bi-pencil-square'],
                    'deadline' => ['warn', 'bi-calendar-event'],
                    default => ['info', 'bi-reply'],
                };
            @endphp
            <li class="is-{{ $tone }}">
                <span class="sc-action-icon" aria-hidden="true"><i class="bi {{ $icon }}"></i></span>
                <div>
                    <strong>{{ $d['title'] ?? 'Update' }}</strong>
                    <small>{{ $d['text'] ?? '' }}</small>
                    <span class="dash-meta">
                        <span><i class="bi bi-building" aria-hidden="true"></i>{{ $d['provider'] ?? '' }}</span>
                        <span><i class="bi bi-clock" aria-hidden="true"></i>{{ $n->created_at?->diffForHumans() }}</span>
                        @if (! $n->read_at)<span class="dash-status is-info">New</span>@endif
                    </span>
                </div>
                @if (! empty($d['url']))<a class="dash-btn is-soft is-sm" href="{{ $d['url'] }}">Open</a>@endif
            </li>
        @endforeach
    </ul>
</section>
@endif
@endsection
