{{-- Donut chart (inline SVG, computed server-side; no script).
     $segments: list of ['label' => string, 'value' => int, 'tone' => good|warn|blue|gray|critical]
                (one colour per meaning, tokens in bakr-dashboard.css)
     $center:   big text in the middle (e.g. "45%"); $centerSub: small text under it
     $title:    accessible name for the chart
     Gaps of 2px separate segments; an empty chart shows a neutral track only. --}}
@php
    $r = 52;
    $c = 2 * M_PI * $r;
    $total = array_sum(array_column($segments, 'value'));
    $live = array_values(array_filter($segments, fn ($s) => $s['value'] > 0));
    $gap = count($live) > 1 ? 2.5 : 0;
    $offset = 0;
@endphp
<svg class="dash-donut" viewBox="0 0 140 140" role="img" aria-label="{{ $title }}: {{ collect($segments)->map(fn ($s) => $s['label'].' '.$s['value'])->implode(', ') }}">
    <circle cx="70" cy="70" r="{{ $r }}" fill="none" stroke="#ece7dd" stroke-width="14"></circle>
    @foreach ($live as $s)
        @php
            $len = $s['value'] / $total * $c;
            $draw = max($len - $gap, 0.5);
        @endphp
        <circle class="dash-donut-seg is-{{ $s['tone'] }}" cx="70" cy="70" r="{{ $r }}" fill="none" stroke-width="14"
                stroke-dasharray="{{ round($draw, 2) }} {{ round($c - $draw, 2) }}" stroke-dashoffset="{{ round(-$offset, 2) }}"
                transform="rotate(-90 70 70)">
            <title>{{ $s['label'] }}: {{ $s['value'] }} ({{ round($s['value'] / $total * 100) }}%)</title>
        </circle>
        @php $offset += $len; @endphp
    @endforeach
    <text x="70" y="{{ isset($centerSub) ? 70 : 78 }}" text-anchor="middle" class="dash-donut-center">{{ $center }}</text>
    @isset($centerSub)
        <text x="70" y="90" text-anchor="middle" class="dash-donut-sub">{{ $centerSub }}</text>
    @endisset
</svg>
