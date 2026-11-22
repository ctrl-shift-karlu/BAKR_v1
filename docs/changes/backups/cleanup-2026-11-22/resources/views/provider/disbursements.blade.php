@extends('provider.layout')

@section('title', 'Disbursements')
@section('photo', 'graduates.jpg')
@section('kicker', $term)
@section('heading')Stipend <em>disbursements</em>@endsection
@section('sub', 'See who has received their stipend for '.$term.' and who has not yet. You release stipends per semester or per month, and only after the scholar\'s requirements for that semester are all approved.')

@section('content')
@php
    $payPill = ['released' => 'is-ok', 'pending' => 'is-warn'];
    $n = fn ($k) => (int) ($summary[$k]->n ?? 0);
    $sum = fn ($k) => (float) ($summary[$k]->total ?? 0);
    $monthName = fn ($ym) => \Illuminate\Support\Carbon::createFromFormat('!Y-m', $ym)->format('M Y');
    $monthRange = $months ? $monthName($months[0]).' – '.$monthName(end($months)) : '';
    // Rows that cannot be released yet: the scholar's requirements for the semester are not all approved.
    $blockers = $rows->getCollection()->mapWithKeys(fn ($d) => [$d->id => $d->status === 'released' ? null : $d->scholar->releaseBlocker($d->term)]);
    $nBlocked = $blockers->filter()->count();
    $reqUrl = $scope->has('requirements') ? route('provider.requirements', ['term' => $term]) : null;
@endphp

<form class="dash-filters" method="GET" action="{{ route('provider.disbursements') }}">
    <div class="dash-field">
        <label for="term">Semester</label>
        <select id="term" name="term" data-autosubmit>
            @foreach ($terms as $t)<option value="{{ $t }}" @selected($t === $term)>{{ $t }}</option>@endforeach
        </select>
    </div>
    <noscript><button type="submit" class="dash-btn is-soft">Show</button></noscript>
</form>

<section class="dash-kpis" aria-label="Summary for {{ $term }}">
    <a class="dash-kpi is-1" href="{{ route('provider.disbursements', ['term' => $term, 'status' => 'released']) }}">
        <span class="dash-kpi-icon"><i class="bi bi-check2-circle" aria-hidden="true"></i></span>
        <span><strong>{{ $n('released') }}</strong><span>Released · ₱{{ number_format($sum('released'), 0) }}</span></span>
    </a>
    <a class="dash-kpi is-3" href="{{ route('provider.disbursements', ['term' => $term, 'status' => 'pending']) }}">
        <span class="dash-kpi-icon"><i class="bi bi-hourglass-split" aria-hidden="true"></i></span>
        <span><strong>{{ $n('pending') }}</strong><span>Not yet released · ₱{{ number_format($sum('pending'), 0) }}</span></span>
    </a>
    <div class="dash-kpi is-2">
        <span class="dash-kpi-icon"><i class="bi bi-people" aria-hidden="true"></i></span>
        <span><strong>{{ $missing }}</strong><span>Scholars not prepared yet</span></span>
    </div>
</section>

@if ($missing || $noAmount)
    <div class="dash-card pv-callout">
        <div>
            @if ($missing)
                <strong>{{ $missing }} {{ \Illuminate\Support\Str::plural('scholar', $missing) }} in good standing {{ $missing === 1 ? 'has' : 'have' }} no stipend record for {{ $term }}.</strong>
            @endif
            @if ($noAmount)
                <p class="pv-muted">{{ $noAmount }} active {{ \Illuminate\Support\Str::plural('scholar', $noAmount) }} {{ $noAmount === 1 ? 'has' : 'have' }} no stipend amount set, so they are skipped. Set it on their scholar page.</p>
            @endif
        </div>
        @if ($missing)
            <form method="POST" action="{{ route('provider.disbursements.generate') }}" class="pv-prepare">
                @csrf
                <input type="hidden" name="term" value="{{ $term }}">
                <div class="dash-field">
                    <label for="release_type">Release</label>
                    <select id="release_type" name="release_type" aria-describedby="release-help">
                        @foreach ($releaseTypes as $key => $label)
                            <option value="{{ $key }}" @selected(old('release_type', 'semester') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="dash-btn is-sun"><i class="bi bi-plus-circle" aria-hidden="true"></i>Prepare {{ $term }}</button>
                <small id="release-help" class="pv-muted pv-prepare-help">Per month makes {{ count($months) }} records per scholar ({{ $monthRange }}), each for the scholar's stipend amount.</small>
            </form>
        @endif
    </div>
@endif

@if ($nBlocked)
    <div class="dash-card pv-callout">
        <div>
            <strong><i class="bi bi-lock" aria-hidden="true"></i> {{ $nBlocked }} {{ \Illuminate\Support\Str::plural('stipend', $nBlocked) }} on this page can't be released yet.</strong>
            <p class="pv-muted">A stipend is released only after every maintaining requirement of {{ $term }} is approved. Review the uploads first; the lock opens by itself.</p>
        </div>
        @if ($reqUrl)<a class="dash-btn is-soft" href="{{ $reqUrl }}"><i class="bi bi-folder-check" aria-hidden="true"></i>Review requirements</a>@endif
    </div>
@endif

<div class="dash-section-head">
    <h2 class="dash-section-title">Scholars</h2>
    <nav class="dash-seg" aria-label="Filter by status">
        <a href="{{ route('provider.disbursements', ['term' => $term]) }}" @if (! $status) aria-current="true" @endif>All</a>
        @foreach (\App\Models\StipendDisbursement::STATUSES as $key => $label)
            <a href="{{ route('provider.disbursements', ['term' => $term, 'status' => $key]) }}" @if ($status === $key) aria-current="true" @endif>{{ $label }}</a>
        @endforeach
    </nav>
</div>

<form method="POST" action="{{ route('provider.disbursements.release') }}" id="bulk-release">@csrf</form>

<div class="dash-table-wrap">
    <table class="dash-table is-many-cols">
        <thead>
            <tr>
                <th scope="col"><label class="dash-check"><input type="checkbox" data-check-all="release" aria-label="Select all not yet released"></label></th>
                <th scope="col">Scholar</th><th scope="col">Scholarship</th><th scope="col">Release</th><th scope="col" class="is-num">Amount</th>
                <th scope="col">Status</th><th scope="col">Released</th><th scope="col">Action</th>
            </tr>
        </thead>
        <tbody>
        @forelse ($rows as $d)
            @php $blocker = $blockers[$d->id] ?? null; @endphp
            <tr>
                <td>
                    @if ($d->status !== 'released' && ! $blocker)
                        <label class="dash-check"><input type="checkbox" name="ids[]" value="{{ $d->id }}" form="bulk-release" data-check="release" aria-label="Select {{ $d->scholar->displayName() }}"></label>
                    @endif
                </td>
                <td>
                    <div class="dash-person">
                        <span class="dash-avatar is-1" aria-hidden="true">{{ $d->scholar->initials() }}</span>
                        <a class="is-link" href="{{ route('provider.scholars.show', $d->scholar->id) }}" title="Open {{ $d->scholar->displayName() }}'s full profile"><strong>{{ $d->scholar->displayName() }}</strong><span class="dash-meta">
                            @if ($d->scholar->scholar_code)<span><i class="bi bi-person-vcard" aria-hidden="true"></i>{{ $d->scholar->scholar_code }}</span>@endif
                            @if ($d->scholar->student_number)<span>{{ $d->scholar->student_number }}</span>@endif
                            @if (! $d->scholar->scholar_code && ! $d->scholar->student_number)<span>{{ $d->scholar->statusLabel() }}</span>@endif
                        </span></a>
                    </div>
                </td>
                <td><span class="dash-pill is-violet">{{ $d->scholar->scholarship->shortName() }}</span></td>
                <td class="pv-nowrap">{{ $d->releaseTypeLabel() }}@if ($d->release_type === 'monthly')<br><small class="pv-muted">{{ $d->periodLabel() }}</small>@endif</td>
                <td class="is-num">₱{{ number_format($d->amount, 2) }}</td>
                <td>
                    <span class="dash-status {{ $payPill[$d->status] ?? '' }}">{{ $d->statusLabel() }}</span>
                    @if ($blocker)<br><small class="pv-muted pv-lock"><i class="bi bi-lock" aria-hidden="true"></i> Locked: {{ $blocker }}</small>@endif
                </td>
                <td>{{ $d->released_at?->format('M j, Y') ?? '—' }}@if ($d->reference)<br><small class="pv-muted">Ref {{ $d->reference }}</small>@endif</td>
                <td>
                    @if ($d->status === 'released')
                        <span class="pv-muted"><i class="bi bi-check2" aria-hidden="true"></i> Done</span>
                    @elseif ($blocker)
                        <button type="button" class="dash-btn is-soft is-sm" disabled><i class="bi bi-lock" aria-hidden="true"></i>Locked</button>
                    @else
                        <form method="POST" action="{{ route('provider.disbursements.release-one', $d->id) }}" class="dash-inline-form pv-pay-form" data-busy="Releasing…">
                            @csrf
                            <label class="visually-hidden" for="ref-{{ $d->id }}">Reference for {{ $d->scholar->displayName() }}</label>
                            <input id="ref-{{ $d->id }}" class="dash-input" name="reference" maxlength="100" value="{{ $d->reference }}" placeholder="Reference (optional)">
                            <button type="submit" class="dash-btn is-sm"><i class="bi bi-send-check" aria-hidden="true"></i>Release</button>
                        </form>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="8"><div class="dash-empty"><i class="bi bi-cash-coin" aria-hidden="true"></i>No stipend records for {{ $term }}{{ $status ? " with this status" : "" }}.</div></td></tr>
        @endforelse
        </tbody>
    </table>
</div>

@if ($rows->contains(fn ($d) => $d->status !== 'released' && ! ($blockers[$d->id] ?? null)))
    <div class="pv-bulk">
        <label class="visually-hidden" for="bulk-ref">Reference for the selected releases</label>
        <input id="bulk-ref" class="dash-input" name="reference" form="bulk-release" maxlength="100" placeholder="Reference (optional, e.g. batch no.)">
        <button type="submit" class="dash-btn" form="bulk-release" data-bulk-submit="release"><i class="bi bi-send-check" aria-hidden="true"></i>Mark selected as released</button>
    </div>
@endif
{{ $rows->links('pagination::bootstrap-5') }}
@endsection
