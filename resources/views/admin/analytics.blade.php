@extends('admin.layout')

{{-- Admin › Analytics (2026-11-19, owner's layout; reworked the same day):
     Headline numbers
     1 Matching          table (students per result, per scholarship) | donut of eligible matches + trivia
     2 Scholars + sign-ups   two charts side by side, each with its own filter buttons:
                         scholars per scholarship / campus (bars) | new accounts per day / week / month / year (line)
     Why students don't qualify, and the providers' follow-up table. Totals only: no student's answers,
     names or free text. Filters are plain links (?scholars=…&signups=…), so they need no script.
     Layout classes (an-*) are in bakr-admin.css and use the shared dashboard tokens. --}}

@section('title', 'Analytics')
@section('photo', 'opt/students2.jpg')
@section('kicker', $term)
@section('heading')<em>Analytics</em>@endsection
@section('sub', 'How students move from sign-up to a match, how your scholars are spread out, and when students join. Totals only.')

@section('content')
@php
    $labels = \App\Models\ScholarshipMatch::STATUS_LABELS;
    $eligible = strtolower($labels['eligible']);
    $pct = fn ($n, $of) => $of ? round($n / $of * 100).'%' : '—';
    $scholarKeys = ['active' => ['Active', 'good'], 'inactive' => ['Inactive', 'warn'], 'terminated' => ['Terminated', 'critical']];
    $rangeText = ['day' => 'the last 30 days', 'week' => 'the last 12 weeks', 'month' => 'the last 12 months', 'year' => 'the last 5 years'];
    $groups = \App\Http\Controllers\Admin\AdminController::SCHOLAR_GROUPS;
    $ranges = \App\Http\Controllers\Admin\AdminController::SIGNUP_RANGES;
    $link = fn (array $q) => route('admin.analytics', array_merge(['scholars' => $scholarGroup, 'signups' => $signupRange], $q));
    $scholarTotals = ['active' => array_sum(array_column($scholarRows, 'active')), 'inactive' => array_sum(array_column($scholarRows, 'inactive')), 'terminated' => array_sum(array_column($scholarRows, 'terminated'))];
@endphp

{{-- ================= EXPORT ================= --}}
{{-- Print and "Save as PDF" both open the browser's print window (pick "Save as PDF" as the printer there);
     the print styles in bakr-admin.css show every row and print in light colours. Excel = a real .xlsx
     with the same totals (AdminController::exportAnalytics). --}}
<div class="an-tools" role="group" aria-label="Print or download this page">
    <span class="an-tools-text">Totals as of {{ now()->format('M j, Y g:i A') }}</span>
    <button type="button" class="dash-btn is-soft is-sm" data-print><i class="bi bi-printer" aria-hidden="true"></i>Print</button>
    <button type="button" class="dash-btn is-soft is-sm" data-print data-print-pdf><i class="bi bi-file-earmark-pdf" aria-hidden="true"></i>Save as PDF</button>
    <a class="dash-btn is-sm" href="{{ route('admin.analytics.export', ['signups' => $signupRange]) }}" download><i class="bi bi-file-earmark-spreadsheet" aria-hidden="true"></i>Download Excel</a>
</div>
<p class="an-pdf-tip" id="an-pdf-tip" hidden role="status"><i class="bi bi-info-circle" aria-hidden="true"></i>In the print window, choose <strong>Save as PDF</strong> as the printer (Destination), then Save.</p>

<header class="an-print-head" aria-hidden="true">
    <img src="{{ asset('images/logo/bakr-logo.png') }}" alt="">
    <div>
        <strong>{{ config('bakr.system_name') }}</strong>
        <span>Analytics · {{ $term }} · printed {{ now()->format('M j, Y g:i A') }}</span>
        <span>Scholars {{ strtolower($groups[$scholarGroup]) }}; sign-ups per {{ $signupRange }}. Totals only.</span>
    </div>
</header>

{{-- ================= HEADLINE ================= --}}
<section class="dash-kpis" aria-label="Headline numbers">
    <div class="dash-kpi is-1">
        <span class="dash-kpi-icon"><i class="bi bi-person-plus" aria-hidden="true"></i></span>
        <span><strong>{{ $headline['students'] }}</strong><span>Student accounts</span></span>
    </div>
    <div class="dash-kpi is-2">
        <span class="dash-kpi-icon"><i class="bi bi-clipboard-check" aria-hidden="true"></i></span>
        <span><strong>{{ $headline['answered'] }}</strong><span>Answered the form · {{ $pct($headline['answered'], $headline['students']) }}</span></span>
    </div>
    <div class="dash-kpi is-3">
        <span class="dash-kpi-icon"><i class="bi bi-patch-check" aria-hidden="true"></i></span>
        <span><strong>{{ $headline['matched'] }}</strong><span>Have at least one match · {{ $pct($headline['matched'], $headline['answered']) }}</span></span>
    </div>
    <div class="dash-kpi is-4">
        <span class="dash-kpi-icon"><i class="bi bi-mortarboard" aria-hidden="true"></i></span>
        <span><strong>{{ $headline['scholars'] }}</strong><span>Current scholars</span></span>
    </div>
</section>

{{-- ================= 1 · MATCHING ================= --}}
<div class="dash-section-head pv-gap" id="matching">
    <div>
        <h2 class="dash-section-title">Matching</h2>
        <p class="an-sub">Every student who answered the form, by their result for each scholarship.</p>
    </div>
</div>

<div class="an-pair">
    <section class="dash-card an-card" aria-labelledby="t-matching">
        <div class="an-card-head"><h3 class="dash-card-title" id="t-matching"><i class="bi bi-table" aria-hidden="true"></i>Students per scholarship</h3></div>
        <p class="an-lead">Sorted by the number of {{ $eligible }} students.</p>
        <div class="dash-table-wrap an-peek" data-peek="5">
            <table class="dash-table an-table">
                <caption class="visually-hidden">Students per result for each scholarship</caption>
                <thead>
                    <tr>
                        <th scope="col">Scholarship</th>
                        <th scope="col" class="is-num">{{ $labels['eligible'] }}</th>
                        <th scope="col" class="is-num">{{ $labels['possible'] }}</th>
                        <th scope="col" class="is-num">{{ $labels['needs_info'] }}</th>
                        <th scope="col" class="is-num">{{ $labels['not_eligible'] }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($matching['rows'] as $r)
                        <tr>
                            <td><strong title="{{ $r['name'] }}">{{ $r['short'] }}</strong></td>
                            <td class="is-num" data-label="{{ $labels['eligible'] }}"><strong>{{ $r['eligible'] }}</strong></td>
                            <td class="is-num" data-label="{{ $labels['possible'] }}">{{ $r['possible'] }}</td>
                            <td class="is-num" data-label="{{ $labels['needs_info'] }}">{{ $r['needs_info'] }}</td>
                            <td class="is-num" data-label="{{ $labels['not_eligible'] }}">{{ $r['not_eligible'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <section class="dash-card an-card" aria-labelledby="c-matching">
        <div class="an-card-head"><h3 class="dash-card-title" id="c-matching"><i class="bi bi-pie-chart" aria-hidden="true"></i>Where {{ $eligible }} matches go</h3></div>
        <p class="an-lead">Each slice is a scholarship's share of all {{ $eligible }} matches.</p>
        <div class="an-donut">
            @include('partials.portal.chart-donut', ['segments' => $matching['segments'], 'center' => $matching['total'], 'centerSub' => $matching['total'] ? 'eligible' : 'none yet', 'title' => 'Eligible matches per scholarship'])
            <ul class="dash-legend">
                @forelse ($matching['segments'] as $s)
                    <li><i class="dash-sw is-{{ $s['tone'] }}"></i><span title="{{ $s['label'] }}">{{ $s['label'] }}</span><b>{{ $s['value'] }}</b></li>
                @empty
                    <li><span>No {{ $eligible }} matches yet.</span></li>
                @endforelse
            </ul>
        </div>

        <div class="an-trivia" role="group" aria-label="Did you know">
            <div class="an-trivia-title"><i class="bi bi-lightbulb" aria-hidden="true"></i>Did you know?</div>
            @if ($matching['most'])
                <div class="an-trivia-grid">
                    <div class="an-fact is-good">
                        <span class="an-fact-tag">Most matched</span>
                        <strong title="{{ $matching['most']['name'] }}">{{ $matching['most']['short'] }}</strong>
                        <span class="an-fact-note">{{ $matching['most']['eligible'] }} {{ str('student')->plural($matching['most']['eligible']) }} {{ $eligible }}</span>
                    </div>
                    @if ($matching['least'] && $matching['least']['short'] !== $matching['most']['short'])
                        <div class="an-fact">
                            <span class="an-fact-tag">Least matched</span>
                            <strong title="{{ $matching['least']['name'] }}">{{ $matching['least']['short'] }}</strong>
                            <span class="an-fact-note">{{ $matching['least']['eligible'] }} {{ str('student')->plural($matching['least']['eligible']) }} {{ $eligible }}</span>
                        </div>
                    @endif
                </div>
            @else
                <p class="an-lead">No student is {{ $eligible }} for a scholarship yet.</p>
            @endif
        </div>
    </section>
</div>

<section class="dash-card an-card pv-gap" aria-labelledby="ch-blockers">
    <div class="an-card-head"><h3 class="dash-card-title" id="ch-blockers"><i class="bi bi-sign-stop" aria-hidden="true"></i>Why students don't qualify</h3></div>
    <p class="an-lead">The requirement “Not eligible” students miss most often, per scholarship.</p>
    @forelse (array_slice($blockers, 0, 6) as $b)
        @if ($loop->first)<ol class="an-list an-peek" data-peek="3">@endif
        <li>
            <span class="dash-pill is-violet">{{ $b['scholarship'] }}</span>
            <span class="an-list-text">{{ $b['rule'] }}</span>
            <b>{{ $b['students'] }}</b>
        </li>
        @if ($loop->last)</ol>@endif
    @empty
        <p class="dash-empty"><i class="bi bi-check2-circle" aria-hidden="true"></i>No “Not eligible” results yet.</p>
    @endforelse
</section>

{{-- ================= 2 · SCHOLARS + SIGN-UPS ================= --}}
<div class="dash-section-head pv-gap" id="charts">
    <div>
        <h2 class="dash-section-title">Scholars and sign-ups</h2>
        <p class="an-sub">How your scholars are spread out, and when students make an account.</p>
    </div>
</div>

<div class="an-duo">
    <section class="dash-card an-card" aria-labelledby="c-scholars" id="scholars">
        <div class="an-card-head">
            <h3 class="dash-card-title" id="c-scholars"><i class="bi bi-bar-chart" aria-hidden="true"></i>Scholars {{ strtolower($groups[$scholarGroup]) }}</h3>
            <nav class="dash-seg is-compact" aria-label="Group scholars by">
                @foreach ($groups as $key => $text)
                    <a href="{{ $link(['scholars' => $key]) }}#scholars" @if ($scholarGroup === $key) aria-current="true" @endif>{{ $text }}</a>
                @endforeach
            </nav>
        </div>
        <p class="an-lead">{{ array_sum($scholarTotals) }} {{ str('scholar')->plural(array_sum($scholarTotals)) }} in all, {{ $scholarTotals['terminated'] }} of them terminated · {{ $scholarTotals['active'] }} active · {{ $scholarTotals['inactive'] }} inactive.</p>
        <div class="an-peek" data-peek="6">
            @include('admin.partials.bars', ['title' => 'Scholars '.strtolower($groups[$scholarGroup]), 'keys' => $scholarKeys, 'rows' => $scholarRows])
        </div>
        @if ($scholarGroup === 'campus')
            <p class="an-note">The campus is the one the provider chose when adding the scholar. “Campus not set” are scholars added earlier whose program is offered at more than one campus; their provider can pick the campus on the scholar's profile.</p>
        @endif
    </section>

    <section class="dash-card an-card" aria-labelledby="c-signups" id="signups">
        <div class="an-card-head">
            <h3 class="dash-card-title" id="c-signups"><i class="bi bi-graph-up" aria-hidden="true"></i>New accounts per {{ $signupRange }}</h3>
            <nav class="dash-seg is-compact" aria-label="Show sign-ups per">
                @foreach ($ranges as $key => $text)
                    <a href="{{ $link(['signups' => $key]) }}#signups" @if ($signupRange === $key) aria-current="true" @endif>{{ $text }}</a>
                @endforeach
            </nav>
        </div>
        <p class="an-lead">{{ $signups['total'] }} new student {{ str('account')->plural($signups['total']) }} in {{ $rangeText[$signupRange] }} · {{ $signups['accounts'] }} in all.</p>
        @include('admin.partials.line', ['title' => 'New student accounts per '.$signupRange, 'points' => $signups['rows'], 'unit' => 'new accounts'])
        <p class="an-note">Each point is one {{ $signupRange }}. Point to it for the exact number.</p>
    </section>
</div>

{{-- ================= PROVIDERS ================= --}}
<div class="dash-section-head pv-gap" id="monitoring">
    <div>
        <h2 class="dash-section-title">Providers · {{ $term }}</h2>
        <p class="an-sub">How each provider's scholars are doing this semester.</p>
    </div>
</div>

<div class="dash-table-wrap an-peek" data-peek="4">
    <table class="dash-table an-table is-wide">
        <caption class="visually-hidden">Scholars per provider this semester</caption>
        <thead>
            <tr>
                <th scope="col">Provider</th>
                <th scope="col" class="is-num">Active</th>
                <th scope="col" class="is-num">Inactive</th>
                <th scope="col" class="is-num">Terminated</th>
                <th scope="col">Documents approved</th>
                <th scope="col">Stipends released</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($providers as $p)
                <tr>
                    <td><span class="an-provider">@if ($p['logo'])<img src="{{ asset($p['logo']) }}" alt="">@endif<strong>{{ $p['name'] }}</strong></span></td>
                    <td class="is-num" data-label="Active">{{ $p['status']['active'] }}</td>
                    <td class="is-num" data-label="Inactive">@if ($p['status']['inactive'])<span class="dash-pill is-warn">{{ $p['status']['inactive'] }}</span>@else 0 @endif</td>
                    <td class="is-num" data-label="Terminated">{{ $p['status']['terminated'] }}</td>
                    <td data-label="Documents approved">
                        @if (! $p['hasReqs'])<small class="pv-muted">In person at the DNSC office</small>
                        @elseif (! $p['reqTotal'])<small class="pv-muted">No documents opened yet</small>
                        @else {{ $p['reqApproved'] }} of {{ $p['reqTotal'] }} <small class="pv-muted">({{ $pct($p['reqApproved'], $p['reqTotal']) }})</small>
                        @endif
                    </td>
                    <td data-label="Stipends released">
                        @if (! $p['hasPay'])<small class="pv-muted">Handled outside BAKR</small>
                        @elseif (! $p['payTotal'])<small class="pv-muted">Not prepared</small>
                        @else {{ $p['payReleased'] }} of {{ $p['payTotal'] }} · ₱{{ number_format($p['amount'], 0) }}
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
<p class="an-note an-foot">Inactive = a required maintaining document is past its due date and not submitted, or marked inactive by a provider that checks documents in person at the DNSC office. A stipend is released only after all of the semester's required documents are approved.</p>
@endsection

@push('scripts')
    <script src="{{ asset('js/bakr-admin-analytics.js') }}" defer></script>
    <script src="{{ asset('js/bakr-admin-print.js') }}" defer></script>
@endpush
