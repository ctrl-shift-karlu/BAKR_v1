{{-- Donut (part of a whole) for the admin analytics. Server-rendered SVG, no chart library.
     $title     what the chart shows (used for the accessible name and the table caption)
     $segments  [['label' => 'Eligible', 'value' => 4, 'tone' => 'good'], …] in a fixed order
     $center    word under the total in the middle, e.g. "results"
     Colours come from the --viz-* tokens (public/css/bakr-admin.css, dark steps in bakr-dark.css).
     Every value is also in the legend and in the "Show as table" view, so colour never carries meaning alone. --}}
@php
    $total = array_sum(array_column($segments, 'value'));
    $shown = array_values(array_filter($segments, fn ($s) => $s['value'] > 0));
    $gap = count($shown) > 1 ? 0.8 : 0;          // 2px of surface between segments
    $offset = 25;                                  // start at 12 o'clock
    $pct = fn ($v) => $total ? round($v / $total * 100) : 0;
    $summary = collect($segments)->map(fn ($s) => $s['label'].' '.$s['value'])->implode(', ');
@endphp
<figure class="viz-donut">
    <div class="viz-donut-chart">
        <svg viewBox="0 0 42 42" role="img" aria-label="{{ $title }}: {{ $total ? $summary : 'no data yet' }}">
            <circle class="viz-track" cx="21" cy="21" r="15.9155"></circle>
            @foreach ($shown as $s)
                @php $len = $s['value'] / $total * 100; $dash = max($len - $gap, 0.2); @endphp
                <circle class="viz-seg is-{{ $s['tone'] }}" cx="21" cy="21" r="15.9155"
                        stroke-dasharray="{{ round($dash, 3) }} {{ round(100 - $dash, 3) }}" stroke-dashoffset="{{ round($offset, 3) }}">
                    <title>{{ $s['label'] }}: {{ $s['value'] }} ({{ $pct($s['value']) }}%)</title>
                </circle>
                @php $offset -= $len; @endphp
            @endforeach
        </svg>
        <div class="viz-donut-center" aria-hidden="true"><strong>{{ number_format($total) }}</strong><span>{{ $center }}</span></div>
    </div>

    <ul class="viz-legend">
        @foreach ($segments as $s)
            <li title="{{ $s['label'] }}: {{ $s['value'] }} ({{ $pct($s['value']) }}%)">
                <span class="viz-swatch is-{{ $s['tone'] }}" aria-hidden="true"></span>
                <span class="viz-legend-label">{{ $s['label'] }}</span>
                <strong>{{ number_format($s['value']) }}</strong>
                <small>{{ $pct($s['value']) }}%</small>
            </li>
        @endforeach
    </ul>
</figure>

<details class="viz-table">
    <summary>Show as table</summary>
    <table class="dash-table">
        <caption class="visually-hidden">{{ $title }}</caption>
        <thead><tr><th scope="col">Group</th><th scope="col" class="is-num">Count</th><th scope="col" class="is-num">Share</th></tr></thead>
        <tbody>
            @foreach ($segments as $s)
                <tr><td>{{ $s['label'] }}</td><td class="is-num">{{ $s['value'] }}</td><td class="is-num">{{ $pct($s['value']) }}%</td></tr>
            @endforeach
            <tr><th scope="row">Total</th><td class="is-num"><strong>{{ $total }}</strong></td><td class="is-num">100%</td></tr>
        </tbody>
    </table>
</details>
