@extends('admin.layout')

@section('title', 'Overview')
@section('kicker', $term)
@section('heading')Admin <em>overview</em>@endsection
@section('sub', 'BAKR at a glance: matching, scholar monitoring, and what needs attention.')

@section('content')
<section class="dash-kpis" aria-label="Totals">
    <a class="dash-kpi is-1" href="{{ route('admin.accounts', ['role' => 'student']) }}">
        <span class="dash-kpi-icon"><i class="bi bi-person" aria-hidden="true"></i></span>
        <span><strong>{{ $totals['students'] }}</strong><span>Student accounts</span></span>
    </a>
    <a class="dash-kpi is-2" href="{{ route('admin.analytics') }}#matching">
        <span class="dash-kpi-icon"><i class="bi bi-clipboard-check" aria-hidden="true"></i></span>
        <span><strong>{{ $totals['answered'] }}</strong><span>Submitted the matching form</span></span>
    </a>
    <a class="dash-kpi is-3" href="{{ route('admin.accounts', ['role' => 'scholar']) }}">
        <span class="dash-kpi-icon"><i class="bi bi-mortarboard" aria-hidden="true"></i></span>
        <span><strong>{{ $totals['scholars'] }}</strong><span>Current scholars</span></span>
    </a>
    <a class="dash-kpi is-4" href="{{ route('admin.analytics') }}#monitoring">
        <span class="dash-kpi-icon"><i class="bi bi-cash-coin" aria-hidden="true"></i></span>
        <span><strong>₱{{ number_format($totals['released'], 0) }}</strong><span>Stipends released this semester</span></span>
    </a>
</section>

<div class="dash-split pv-gap">
    <section class="dash-card" aria-labelledby="attn-title">
        <h2 class="dash-card-title" id="attn-title"><i class="bi bi-bell" aria-hidden="true"></i>Needs attention</h2>
        @forelse ($attention as [$tone, $icon, $text, $hint, $url])
            @if ($loop->first)<ul class="dash-list">@endif
            <li>
                <span class="dash-avatar {{ ['danger' => 'is-4', 'warn' => 'is-3', 'info' => 'is-2'][$tone] ?? 'is-1' }}" aria-hidden="true"><i class="bi {{ $icon }}"></i></span>
                <div><strong>{{ $text }}</strong><small>{{ $hint }}</small></div>
                <a class="dash-btn is-soft is-sm" href="{{ $url }}">Open</a>
            </li>
            @if ($loop->last)</ul>@endif
        @empty
            <p class="dash-empty"><i class="bi bi-check2-all" aria-hidden="true"></i>Nothing needs attention right now.</p>
        @endforelse
    </section>

    <section class="dash-card" id="system" aria-labelledby="sys-title">
        <h2 class="dash-card-title" id="sys-title"><i class="bi bi-gear" aria-hidden="true"></i>System</h2>
        <ul class="dash-list">
            <li><span class="dash-avatar is-1" aria-hidden="true"><i class="bi bi-calendar3"></i></span><div><strong>Current semester</strong><small>{{ $system['term'] }}</small></div></li>
            <li><span class="dash-avatar is-2" aria-hidden="true"><i class="bi bi-envelope"></i></span><div><strong>E-mail</strong><small>{{ $system['mail'] }}</small></div></li>
            <li><span class="dash-avatar is-3" aria-hidden="true"><i class="bi bi-chat-square-text"></i></span><div><strong>AI explanations</strong><small>{{ $system['ai'] }}</small></div></li>
            <li><span class="dash-avatar is-4" aria-hidden="true"><i class="bi bi-building"></i></span><div><strong>Providers</strong><small>{{ $totals['providers'] }} active provider {{ \Illuminate\Support\Str::plural('account', $totals['providers']) }}</small></div></li>
        </ul>
    </section>
</div>
@endsection
