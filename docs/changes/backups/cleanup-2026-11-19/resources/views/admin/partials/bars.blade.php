{{-- Horizontal bars (one series: compare amounts) for the admin analytics. No chart library.
     $title  what the chart shows (accessible name + table caption)
     $rows   [['label' => 'Answered the form', 'value' => 12, 'display' => '12 (60%)'], …]  display is optional
     $unit   column title for the value in the table view, e.g. "Students"
     One hue only (--viz-blue): colour does not encode anything here, the length does. --}}
@php
    $max = max(array_merge([0], array_column($rows, 'value')));
    $show = fn ($r) => $r['display'] ?? number_format($r['value']);
@endphp
@if (! $rows)
    <p class="dash-empty"><i class="bi bi-bar-chart" aria-hidden="true"></i>No data yet.</p>
@else
    <ul class="viz-bars" aria-label="{{ $title }}">
        @foreach ($rows as $r)
            <li title="{{ $r['label'] }}: {{ $show($r) }}">
                <span class="viz-bar-label">{{ $r['label'] }}</span>
                <span class="viz-bar-track" aria-hidden="true"><span class="viz-bar-fill" style="width: {{ $max ? round($r['value'] / $max * 100, 1) : 0 }}%"></span></span>
                <span class="viz-bar-value">{{ $show($r) }}</span>
            </li>
        @endforeach
    </ul>

    <details class="viz-table">
        <summary>Show as table</summary>
        <table class="dash-table">
            <caption class="visually-hidden">{{ $title }}</caption>
            <thead><tr><th scope="col">Item</th><th scope="col" class="is-num">{{ $unit }}</th></tr></thead>
            <tbody>
                @foreach ($rows as $r)<tr><td>{{ $r['label'] }}</td><td class="is-num">{{ $show($r) }}</td></tr>@endforeach
            </tbody>
        </table>
    </details>
@endif
