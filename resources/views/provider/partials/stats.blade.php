{{-- "This term at a glance" as statistics: two donuts (stipends, requirements; only for providers
     that use those pages) and a bar list of scholars per scholarship. Aggregates only.
     Colours = the one-colour-per-meaning tokens in bakr-dashboard.css (same as the pills and the
     admin charts). Gold is under 3:1 contrast, so every chart carries a legend with counts. --}}
@php
    $pay = ['released' => $sum('pay', 'released'), 'pending' => $sum('pay', 'pending')];
    $req = ['approved' => $sum('reqs', 'approved'), 'submitted' => $sum('reqs', 'submitted'), 'needs_revision' => $sum('reqs', 'needs_revision'), 'missing' => $sum('reqs', 'missing')];
    $paySegs = [
        ['label' => 'Released', 'value' => $pay['released'], 'tone' => 'good'],
        ['label' => 'Not yet released', 'value' => $pay['pending'], 'tone' => 'warn'],
    ];
    $reqSegs = [
        ['label' => 'Approved', 'value' => $req['approved'], 'tone' => 'good'],
        ['label' => 'Waiting for review', 'value' => $req['submitted'], 'tone' => 'blue'],
        ['label' => 'Needs revision', 'value' => $req['needs_revision'], 'tone' => 'warn'],
        ['label' => 'Not submitted', 'value' => $req['missing'], 'tone' => 'gray'],
    ];
    $scholarBars = $scholarships->map(fn ($s) => ['label' => $s->shortName(), 'title' => $s->name, 'value' => $perScholarship[$s->id]['scholars']])->values();
    $scholarMax = max(1, $scholarBars->max('value'));
    $pctOf = fn ($n, $of) => $of ? (int) round($n / $of * 100) : 0;
@endphp

<section class="dash-stats" aria-label="This term at a glance">
    {{-- Stipends (only for providers that use Disbursements) --}}
    @if ($scope->has('disbursements'))
    <a class="dash-stat-card" href="{{ route('provider.disbursements') }}">
        <header><span class="dash-stat-kicker">Stipends this semester</span><span class="dash-stat-total">{{ $payAll }}</span></header>
        <div class="dash-stat-body">
            @include('partials.portal.chart-donut', ['segments' => $paySegs, 'center' => $payAll ? $pctOf($pay['released'], $payAll).'%' : '—', 'centerSub' => $payAll ? 'released' : 'none yet', 'title' => 'Stipends this semester'])
            <ul class="dash-legend">
                @foreach ($paySegs as $s)
                    <li><i class="dash-sw is-{{ $s['tone'] }}"></i><span>{{ $s['label'] }}</span><b>{{ $s['value'] }}</b></li>
                @endforeach
            </ul>
        </div>
        <footer>₱{{ number_format($releasedTotal, 0) }} released</footer>
    </a>
    @endif

    {{-- Requirements (only for providers that use Requirements) --}}
    @if ($scope->has('requirements'))
    <a class="dash-stat-card" href="{{ route('provider.requirements') }}">
        <header><span class="dash-stat-kicker">Documents this semester</span><span class="dash-stat-total">{{ $reqAll }}</span></header>
        <div class="dash-stat-body">
            @include('partials.portal.chart-donut', ['segments' => $reqSegs, 'center' => $reqAll ? $pctOf($req['approved'], $reqAll).'%' : '—', 'centerSub' => $reqAll ? 'approved' : 'none yet', 'title' => 'Documents this semester'])
            <ul class="dash-legend">
                @foreach ($reqSegs as $s)
                    <li><i class="dash-sw is-{{ $s['tone'] }}"></i><span>{{ $s['label'] }}</span><b>{{ $s['value'] }}</b></li>
                @endforeach
            </ul>
        </div>
        <footer>{{ $req['submitted'] }} waiting for your review</footer>
    </a>
    @endif

    {{-- Scholars per scholarship --}}
    <a class="dash-stat-card" href="{{ route('provider.scholars') }}">
        <header><span class="dash-stat-kicker">Current scholars</span><span class="dash-stat-total">{{ $scholarBars->sum('value') }}</span></header>
        <ul class="dash-hbars" aria-label="Current scholars per scholarship">
            @foreach ($scholarBars as $b)
                <li title="{{ $b['title'] }}: {{ $b['value'] }}">
                    <span>{{ $b['label'] }}</span>
                    <span class="dash-hbar"><i data-w="{{ round($b['value'] / $scholarMax * 100, 1) }}"></i></span>
                    <b>{{ $b['value'] }}</b>
                </li>
            @endforeach
        </ul>
        <footer>{{ $standing['active'] }} active · {{ $standing['inactive'] }} inactive · {{ $standing['terminated'] }} terminated</footer>
    </a>
</section>
