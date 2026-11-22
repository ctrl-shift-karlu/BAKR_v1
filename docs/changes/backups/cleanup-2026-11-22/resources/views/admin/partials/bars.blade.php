{{-- Horizontal bar chart, bars scaled against the largest row (so rows can be compared), each bar
     split into the same status colours as everywhere else. No chart library.
     $title  what the chart shows (accessible name)
     $keys   ['active' => ['Active', 'good'], …]  legend + segment order
     $rows   [['label' => 'TDP', 'sub' => 'full name', 'active' => 3, …], …]
     Exact numbers: tooltip on each segment, the number at the end of the bar, and the table beside it. --}}
@php
    $sum = fn ($r) => array_sum(array_map(fn ($k) => (int) ($r[$k] ?? 0), array_keys($keys)));
    $max = max(1, ...array_map($sum, $rows ?: [[]]));
@endphp
@if (! array_filter(array_map($sum, $rows)))
    <p class="dash-empty"><i class="bi bi-bar-chart" aria-hidden="true"></i>No scholars yet.</p>
@else
    <ul class="viz-legend is-inline" aria-label="Legend">
        @foreach ($keys as [$label, $tone])
            <li><span class="viz-swatch is-{{ $tone }}" aria-hidden="true"></span><span class="viz-legend-label">{{ $label }}</span></li>
        @endforeach
    </ul>
    <ul class="viz-stack is-absolute" aria-label="{{ $title }}">
        @foreach ($rows as $r)
            @php $total = $sum($r); @endphp
            <li>
                <span class="viz-bar-label">{{ $r['label'] }}</span>
                @if ($total)
                    <span class="viz-stack-track" style="width: {{ round($total / $max * 100, 2) }}%">
                        @foreach ($keys as $key => [$label, $tone])
                            @php $v = (int) ($r[$key] ?? 0); @endphp
                            @continue($v === 0)
                            <span class="viz-seg-bar is-{{ $tone }}" style="flex: {{ $v }} 1 0" title="{{ $r['label'] }} · {{ $label }}: {{ $v }}"></span>
                        @endforeach
                    </span>
                @else
                    <span class="viz-stack-track is-empty"><span>None yet</span></span>
                @endif
                <span class="viz-bar-value">{{ $total }}</span>
            </li>
        @endforeach
    </ul>
@endif
