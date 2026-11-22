{{-- Public, aggregate-only snapshot of who uses BAKR. Data comes from PublicAnalyticsService (live, every load).
     A chart whose groups are all under the privacy minimum says so ($few) instead of looking empty or stale. --}}
@php
    $few = fn (array $rows) => ! collect($rows)->contains(fn ($r) => ! $r['hidden']);
    $fewNote = 'Not enough students yet: this chart fills in once a group has '.$insights['min_group'].' or more.';
@endphp
<section class="nx-section bk-insights" id="insights" aria-labelledby="insightsTitle">
    <div class="container">
        <div class="bk-insights-head">
            <h2 class="nx-h2" id="insightsTitle">Who uses BAKR</h2>
            <p class="nx-lead">A general picture of the students who use BAKR, so the DNSC community can see how it helps. You only see anonymous totals.</p>
            @if ($insights['as_of'])
                <p class="bk-sample-badge bk-live"><i class="bi bi-clock-history" aria-hidden="true"></i> Updated {{ $insights['as_of'] }}</p>
            @endif
        </div>

        <div class="bk-insight-totals">
            @foreach ($insights['totals'] as $t)
                <div class="bk-insight-total">
                    <i class="bi {{ $t['icon'] }}" aria-hidden="true"></i>
                    <strong>{{ number_format($t['value']) }}</strong>
                    <span>{{ $t['label'] }}</span>
                </div>
            @endforeach
        </div>

        <div class="bk-chart-grid">

            {{-- 1. Line + area: a trend over time --}}
            <article class="bk-insight-card bk-span-2">
                <h3>{{ $insights['charts']['trend']['title'] }}</h3>
                <p class="bk-chart-note">{{ $insights['charts']['trend']['note'] }}</p>
                <svg class="bk-line" viewBox="0 0 600 220" role="img" aria-label="Line chart of student accounts by month, rising from {{ $insights['charts']['trend']['geo']['first'] }} to {{ $insights['charts']['trend']['geo']['last'] }}">
                    <line x1="40" y1="190" x2="580" y2="190" class="bk-axis"/>
                    <line x1="40" y1="110" x2="580" y2="110" class="bk-grid"/>
                    <line x1="40" y1="30" x2="580" y2="30" class="bk-grid"/>
                    <text x="34" y="34" class="bk-tick" text-anchor="end">{{ $insights['charts']['trend']['geo']['max'] }}</text>
                    <text x="34" y="114" class="bk-tick" text-anchor="end">{{ (int) round($insights['charts']['trend']['geo']['max'] / 2) }}</text>
                    <text x="34" y="194" class="bk-tick bk-tick-zero" text-anchor="end">0</text>
                    <path d="{{ $insights['charts']['trend']['geo']['area'] }}" class="bk-line-area"/>
                    <path d="{{ $insights['charts']['trend']['geo']['line'] }}" class="bk-line-path" pathLength="1"/>
                    @foreach ($insights['charts']['trend']['geo']['points'] as $pt)
                        <circle cx="{{ $pt['x'] }}" cy="{{ $pt['y'] }}" r="4.5" class="bk-dot"><title>{{ $pt['label'] }}: {{ $pt['value'] }}</title></circle>
                        <text x="{{ $pt['x'] }}" y="212" class="bk-tick" text-anchor="middle">{{ $pt['label'] }}</text>
                    @endforeach
                </svg>
            </article>

            {{-- 2. Donut: parts of a whole, few categories --}}
            <article class="bk-insight-card">
                <h3>{{ $insights['charts']['campus']['title'] }}</h3>
                <p class="bk-chart-note">{{ $few($insights['charts']['campus']['rows']) ? $fewNote : $insights['charts']['campus']['note'] }}</p>
                <div class="bk-donut-wrap">
                    <svg class="bk-donut" viewBox="0 0 42 42" role="img" aria-label="Donut chart of students by campus">
                        <circle cx="21" cy="21" r="15.9155" class="bk-donut-track"/>
                        @foreach ($insights['charts']['campus']['rows'] as $i => $row)
                            @if (! $row['hidden'])
                                <circle cx="21" cy="21" r="15.9155" class="bk-donut-seg bk-c{{ $i }}" stroke-dasharray="{{ $row['dash'] }} {{ 100 - $row['dash'] }}" stroke-dashoffset="{{ $row['offset'] }}"/>
                            @endif
                        @endforeach
                    </svg>
                    <div class="bk-donut-centre"><strong>{{ number_format($insights['totals'][0]['value']) }}</strong><span>students</span></div>
                </div>
                <ul class="bk-legend">
                    @foreach ($insights['charts']['campus']['rows'] as $i => $row)
                        <li>
                            <i class="bk-swatch bk-c{{ $i }}" aria-hidden="true"></i>
                            <span>{{ $row['label'] }}</span>
                            @if ($row['hidden'])
                                <em>Fewer than {{ $insights['min_group'] }}</em>
                            @else
                                <strong>{{ $row['percent'] }}%</strong>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </article>

            {{-- 3. Columns: an ordered scale (year level) --}}
            <article class="bk-insight-card">
                <h3>{{ $insights['charts']['year_levels']['title'] }}</h3>
                <p class="bk-chart-note">{{ $few($insights['charts']['year_levels']['rows']) ? $fewNote : $insights['charts']['year_levels']['note'] }}</p>
                <div class="bk-cols" role="img" aria-label="Column chart of students by year level" style="--cols: {{ count($insights['charts']['year_levels']['rows']) }}">
                    @foreach ($insights['charts']['year_levels']['rows'] as $row)
                        <div class="bk-col">
                            <strong>{{ $row['hidden'] ? '—' : $row['count'] }}</strong>
                            <span class="bk-col-bar"><span style="--h: {{ $row['height'] }}%"></span></span>
                            <small>{{ str_replace(' or higher', '+', $row['label']) }}</small>
                        </div>
                    @endforeach
                </div>
            </article>

            {{-- 4. Horizontal bars: ranked list with long labels --}}
            <article class="bk-insight-card">
                <h3>{{ $insights['charts']['programs']['title'] }}</h3>
                <p class="bk-chart-note">{{ $few($insights['charts']['programs']['rows']) ? $fewNote : $insights['charts']['programs']['note'] }}</p>
                <ul class="bk-bars">
                    @foreach ($insights['charts']['programs']['rows'] as $row)
                        <li>
                            <div class="bk-bar-label">
                                <span>{{ $row['label'] }}</span>
                                @if ($row['hidden'])
                                    <em>Fewer than {{ $insights['min_group'] }}</em>
                                @else
                                    <strong>{{ $row['percent'] }}%</strong>
                                @endif
                            </div>
                            <div class="bk-bar" aria-hidden="true"><span style="--w: {{ $row['hidden'] ? 0 : $row['percent'] }}%"></span></div>
                        </li>
                    @endforeach
                </ul>
            </article>

            {{-- 5. Horizontal bars: scholarships profiles match most (share of all profiles) --}}
            @php $top = $insights['charts']['top_scholarships']; @endphp
            <article class="bk-insight-card">
                <h3>{{ $top['title'] }}</h3>
                <p class="bk-chart-note">{{ $few($top['rows']) ? $fewNote : $top['note'] }}</p>
                @if ($top['rows'])
                    <ul class="bk-bars">
                        @foreach ($top['rows'] as $row)
                            <li>
                                <div class="bk-bar-label">
                                    <span>{{ $row['label'] }}</span>
                                    @if ($row['hidden'])
                                        <em>Fewer than {{ $insights['min_group'] }}</em>
                                    @else
                                        <strong>{{ $row['percent'] }}%</strong>
                                    @endif
                                </div>
                                <div class="bk-bar" aria-hidden="true"><span style="--w: {{ $row['hidden'] ? 0 : $row['percent'] }}%"></span></div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="bk-chart-empty"><i class="bi bi-hourglass-split" aria-hidden="true"></i> No eligible or nearly eligible results yet.</p>
                @endif
            </article>

            {{-- 6. 100% stacked bar: every matching result by outcome. Segments grow by their count (flex-grow),
                 so the bar always adds up; small groups are pooled into one striped "not shown" segment. --}}
            @php
                $out = $insights['charts']['outcomes'];
                $shownCount = collect($out['rows'])->where('hidden', false)->sum('count');
                $pooled = $out['total'] - $shownCount;
            @endphp
            <article class="bk-insight-card bk-span-2">
                <h3>{{ $out['title'] }}</h3>
                <p class="bk-chart-note">{{ $few($out['rows']) ? str_replace('students', 'results', $fewNote) : $out['note'] }}</p>
                <div class="bk-stack" role="img" aria-label="Stacked bar of matching results: @foreach ($out['rows'] as $row){{ $row['label'] }} {{ $row['hidden'] ? 'fewer than '.$insights['min_group'] : $row['percent'].'%' }}{{ $loop->last ? '' : ', ' }}@endforeach">
                    @foreach ($out['rows'] as $row)
                        @if (! $row['hidden'] && $row['count'] > 0)
                            <span class="bk-stack-seg bk-tone-{{ $row['tone'] }} {{ $row['percent'] < 18 ? 'is-narrow' : '' }} {{ $row['percent'] < 7 ? 'is-tiny' : '' }}" style="--g: {{ $row['count'] }}">{{ $row['percent'] }}%</span>
                        @endif
                    @endforeach
                    @if ($pooled > 0 && $shownCount > 0)
                        <span class="bk-stack-seg is-pooled" style="--g: {{ $pooled }}"></span>
                    @endif
                </div>
                <ul class="bk-legend bk-legend-grid">
                    @foreach ($out['rows'] as $row)
                        <li>
                            <i class="bk-swatch bk-tone-{{ $row['tone'] }}" aria-hidden="true"></i>
                            <span>{{ $row['label'] }}</span>
                            @if ($row['hidden'])
                                <em>Fewer than {{ $insights['min_group'] }}</em>
                            @else
                                <strong>{{ $row['percent'] }}%</strong>
                            @endif
                        </li>
                    @endforeach
                </ul>
                @if ($out['total'] > 0)
                    <p class="bk-chart-foot">
                        <i class="bi bi-info-circle" aria-hidden="true"></i>
                        <span>
                            Based on {{ number_format($out['total']) }} results from {{ number_format($out['profiles']) }} {{ \Illuminate\Support\Str::plural('profile', $out['profiles']) }}.
                            @if ($out['with_match'] !== null)
                                {{ number_format($out['with_match']) }} of them can apply for at least one scholarship (eligible or nearly eligible).
                            @endif
                        </span>
                    </p>
                @endif
            </article>
        </div>

        <aside class="bk-privacy-note" aria-label="How privacy is protected">
            <i class="bi bi-shield-lock-fill" aria-hidden="true"></i>
            <div>
                <strong>Your privacy comes first.</strong>
                <p>This page shows only combined totals. It never shows anyone's name, contact details, grades, income or documents. Any group with fewer than {{ $insights['min_group'] }} students is hidden, so nobody, including you, can be picked out.</p>
            </div>
        </aside>
    </div>
</section>
