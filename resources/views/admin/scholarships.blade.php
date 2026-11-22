@extends('admin.layout')

@section('title', 'Scholarships')
@section('photo', 'graduates.jpg')
@section('heading')<em>Scholarships</em>@endsection
@section('sub', 'A health check of the catalogue. Providers edit their own scholarships; this page is read-only.')

@section('content')
@php
    $call = ['open' => ['Open', 'is-ok'], 'upcoming' => ['Upcoming', 'is-info'], 'closed' => ['Closed', '']];
@endphp
<div class="dash-table-wrap">
    <table class="dash-table">
        <caption class="visually-hidden">Scholarships</caption>
        <thead>
            <tr>
                <th scope="col">Scholarship</th>
                <th scope="col">Applications</th>
                <th scope="col">Last checked against the official source</th>
                <th scope="col">Maintaining documents</th>
                <th scope="col" class="is-num">Scholars</th>
                <th scope="col">Provider edits</th>
            </tr>
        </thead>
        <tbody>
        @foreach ($scholarships as $s)
            @php
                $status = $s->callStatus();
                $old = ! $s->verified_on || $s->verified_on->lt(now()->subMonths(6));
                $last = $s->revisions->first();
            @endphp
            <tr>
                <td><strong>{{ $s->shortName() }}</strong> @unless ($s->is_active)<span class="dash-pill is-danger">Hidden</span>@endunless<br><small class="pv-muted">{{ $s->provider }}</small></td>
                <td>@if ($status)<span class="dash-pill {{ $call[$status][1] ?? '' }}">{{ $call[$status][0] ?? ucfirst($status) }}</span>@else<span class="pv-muted">No dates set</span>@endif</td>
                <td>{{ $s->verified_on?->format('M j, Y') ?? '—' }}@if ($old)<br><span class="dash-pill is-warn">Check again</span>@endif</td>
                <td>{{ count($s->maintainingDocuments()) ?: '—' }}@if (! $s->maintainingDocuments())<br><small class="pv-muted">None listed</small>@endif</td>
                <td class="is-num">{{ $s->scholars_count }}</td>
                <td>{{ $s->revisions_count }}@if ($last)<br><small class="pv-muted">last {{ $last->created_at?->format('M j, Y') }}</small>@endif</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
<p class="pv-muted pv-gap">"Check again" appears when the official source was last checked more than 6 months ago. <a href="{{ route('scholarships') }}" target="_blank" rel="noopener">Open the public Scholarships page</a>.</p>
@endsection
