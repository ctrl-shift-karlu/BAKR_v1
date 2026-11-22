{{-- Line chart, no script and no library.
     $title   accessible name of the chart
     $points  [['label' => 'Nov 3', 'full' => 'Week of Nov 3, 2026', 'value' => 4], …] oldest first
     $unit    what one point counts ("new accounts"), used in the tooltips
     Only the line and its area are SVG (stretched to the card with non-scaling strokes). The dots and ALL
     text are HTML placed by percentage, so labels are always the real --text-xs size and never grow or
     overlap when the card is wide. Labels thin out on narrow cards (.is-minor). The numbers are in the tooltips. --}}
@php
    $n = count($points);
    $max = max(array_column($points, 'value') ?: [0]);
    $top = max(4, $max);
    $step = 1;
    foreach ([1, 2, 5, 10, 20, 50, 100, 200, 500, 1000, 2000, 5000, 10000] as $candidate) {
        $step = $candidate;
        if ($top / $candidate <= 4) {
            break;
        }
    }
    $top = (int) ceil($top / $step) * $step;
    $x = fn ($i) => $n > 1 ? $i / ($n - 1) * 100 : 50;          // % from the left of the plot
    $y = fn ($v) => $v / $top * 100;                              // % from the bottom of the plot
    $path = collect($points)->map(fn ($p, $i) => ($i ? 'L' : 'M').round($x($i), 2).' '.round(100 - $y($p['value']), 2))->implode(' ');
    $area = $n ? $path.' L'.round($x($n - 1), 2).' 100 L'.round($x(0), 2).' 100 Z' : '';
    $every = max(1, (int) ceil($n / 6));        // wide card: about six labels
    $everyNarrow = max(1, (int) ceil($n / 3));  // narrow card: about three
@endphp
<div class="viz-line" role="img" aria-label="{{ $title }}: {{ collect($points)->map(fn ($p) => $p['full'].' '.$p['value'])->implode(', ') }}">
    <div class="viz-plot">
        <svg class="viz-svg" viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true" focusable="false">
            @for ($g = 0; $g <= $top; $g += $step)
                <line class="viz-grid" x1="0" x2="100" y1="{{ round(100 - $y($g), 2) }}" y2="{{ round(100 - $y($g), 2) }}"></line>
            @endfor
            @if ($n)
                <path class="viz-area" d="{{ $area }}"></path>
                <path class="viz-path" d="{{ $path }}"></path>
            @endif
        </svg>
        @for ($g = 0; $g <= $top; $g += $step)
            <span class="viz-ylab" style="bottom: {{ round($y($g), 2) }}%">{{ $g }}</span>
        @endfor
        @foreach ($points as $i => $p)
            <span class="viz-dot" style="left: {{ round($x($i), 2) }}%; bottom: {{ round($y($p['value']), 2) }}%" title="{{ $p['full'] }}: {{ $p['value'] }} {{ $unit }}"></span>
            @if ($i % $every === 0 || $i % $everyNarrow === 0)
                <span class="viz-xlab @if ($i % $everyNarrow !== 0) is-minor @endif" style="left: {{ round($x($i), 2) }}%">{{ $p['label'] }}</span>
            @endif
        @endforeach
    </div>
</div>
