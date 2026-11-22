@extends('provider.layout')

@php
    $covers = ['graduates.jpg', 'opt/students2.jpg', 'opt/lib.jpg', 'opt/student.jpg', 'dnsc_campus.jpg', 'opt/gate2.jpg'];
    $sum = fn ($key, $status) => collect($perScholarship)->sum(fn ($p) => $p[$key][$status] ?? 0);
    $payAll = $sum('pay', 'released') + $sum('pay', 'pending');
    $reqAll = $sum('reqs', 'approved') + $sum('reqs', 'submitted') + $sum('reqs', 'needs_revision') + $sum('reqs', 'missing');
    $pct = fn ($n, $of) => $of ? round($n / $of * 100, 1) : 0;
    $hour = (int) now()->format('G');
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
    $hasPay = $scope->has('disbursements');
    $hasReqs = $scope->has('requirements');
@endphp

@section('title', 'Overview')
@section('photo', 'dnsc_campus.jpg')
@section('kicker', $term)
@section('heading'){{ $greeting }}, <em>{{ $provider['name'] }}</em>@endsection
@section('sub', $hasPay || $hasReqs ? 'Your scholars, stipends and documents for this semester at a glance.' : 'Your scholars for this semester at a glance.')
@section('actions')
    <a class="dash-btn is-sun" href="{{ route('provider.scholars') }}"><i class="bi bi-mortarboard" aria-hidden="true"></i>Scholars</a>
    @if ($hasPay)
        <a class="dash-btn is-soft" href="{{ route('provider.disbursements') }}"><i class="bi bi-cash-coin" aria-hidden="true"></i>Stipends</a>
    @endif
    <a class="dash-btn is-soft" href="{{ route('provider.scholars.create') }}"><i class="bi bi-person-plus" aria-hidden="true"></i>Add scholars</a>
@endsection

@section('content')
@if ($scholarships->isEmpty())
    <div class="dash-card dash-empty"><i class="bi bi-inbox" aria-hidden="true"></i>No active scholarships are listed for {{ $provider['name'] }} yet. Ask the BAKR team to set up your scholarships.</div>
@else
    {{-- This term at a glance, as charts (provider/partials/stats) --}}
    @include('provider.partials.stats')

    {{-- One photo card per scholarship --}}
    <div class="dash-section-head"><h2 class="dash-section-title">Your scholarships</h2><a class="dash-btn is-soft is-sm" href="{{ route('provider.scholarships') }}"><i class="bi bi-pencil" aria-hidden="true"></i>Edit details</a></div>
    <section class="dash-photo-cards" aria-label="Your scholarships">
        @foreach ($scholarships as $i => $s)
            @php
                $p = $perScholarship[$s->id];
                $payTotal = array_sum($p['pay']);
                $reqTotal = array_sum($p['reqs']);
            @endphp
            <article class="dash-photo-card" id="sch-{{ $s->slug }}">
                <div class="dash-photo-cover">
                    <img src="{{ asset('images/img/'.$covers[$i % count($covers)]) }}" alt="" loading="lazy" decoding="async">
                    <span class="dash-photo-chip">{{ $s->sectorLabel() }}</span>
                    <span class="dash-photo-code">{{ $s->shortName() }}</span>
                    <img class="dash-photo-logo" src="{{ asset($s->logoPath()) }}" alt="">
                </div>
                <div class="dash-photo-body">
                    <h3>{{ $s->name }}</h3>
                    <div class="dash-photo-meta">
                        <span><b>{{ $p['scholars'] }}</b>current {{ \Illuminate\Support\Str::plural('scholar', $p['scholars']) }}</span>
                        @if ($hasReqs)<span><b>{{ $p['reqs']['submitted'] }}</b>to review</span>@endif
                    </div>
                    @if ($hasPay)
                        <div class="dash-photo-stat">
                            <span>Stipends released <b>{{ $p['pay']['released'] }}/{{ $payTotal }}</b></span>
                            <div class="dash-progress" aria-hidden="true"><i data-w="{{ $pct($p['pay']['released'], $payTotal) }}"></i></div>
                        </div>
                    @endif
                    @if ($hasReqs)
                        <div class="dash-photo-stat">
                            <span>Requirements approved <b>{{ $p['reqs']['approved'] }}/{{ $reqTotal }}</b></span>
                            <div class="dash-progress" aria-hidden="true"><i data-w="{{ $pct($p['reqs']['approved'], $reqTotal) }}"></i><i class="is-info" data-w="{{ $pct($p['reqs']['submitted'], $reqTotal) }}"></i><i class="is-warn" data-w="{{ $pct($p['reqs']['needs_revision'], $reqTotal) }}"></i></div>
                        </div>
                    @endif
                </div>
                <div class="dash-photo-foot">
                    <a class="dash-btn is-sm" href="{{ route('provider.scholars', ['scholarship' => $s->id]) }}">Scholars</a>
                    <a class="dash-btn is-soft is-sm" href="{{ route('provider.scholarships', ['s' => $s->id]) }}">Details</a>
                </div>
            </article>
        @endforeach
    </section>

    <div class="dash-split pv-gap">
        <section class="dash-card">
            <h3 class="dash-card-title"><i class="bi bi-mortarboard" aria-hidden="true"></i>Recently added scholars</h3>
            @forelse ($recentScholars as $sc)
                @if ($loop->first)<ul class="dash-list">@endif
                <li>
                    <span class="dash-avatar is-1" aria-hidden="true">{{ $sc->scholarship->shortName() }}</span>
                    <div>
                        <strong>{{ $sc->displayName() }}</strong>
                        <span class="dash-meta">
                            <span class="dash-status {{ $sc->statusTone() }}">{{ $sc->statusLabel() }}</span>
                            <span><i class="bi bi-award" aria-hidden="true"></i>{{ $sc->scholarship->name }}</span>
                            <span><i class="bi bi-clock" aria-hidden="true"></i>Added {{ $sc->created_at?->diffForHumans() }}</span>
                        </span>
                    </div>
                    <a class="dash-btn is-soft is-sm" href="{{ route('provider.scholars.show', $sc->id) }}"><i class="bi bi-person-lines-fill" aria-hidden="true"></i>View profile</a>
                </li>
                @if ($loop->last)</ul>@endif
            @empty
                <p class="dash-empty"><i class="bi bi-people" aria-hidden="true"></i>No scholars yet. Add the official scholars you already have.</p>
            @endforelse
            <a class="dash-btn is-sm pv-mt" href="{{ route('provider.scholars.create') }}"><i class="bi bi-person-plus" aria-hidden="true"></i>Add scholars</a>
        </section>
        @if ($hasReqs)
        <section class="dash-card">
            <h3 class="dash-card-title"><i class="bi bi-folder-check" aria-hidden="true"></i>Uploads to review</h3>
            @forelse ($awaitingReview as $r)
                @if ($loop->first)<ul class="dash-list">@endif
                <li>
                    <span class="dash-avatar is-2" aria-hidden="true">{{ $r->scholar->scholarship->shortName() }}</span>
                    <div>
                        <strong>{{ $r->scholar->displayName() }}</strong>
                        <span class="dash-meta">
                            <span><i class="bi bi-file-earmark-text" aria-hidden="true"></i>{{ $r->name }}</span>
                            <span><i class="bi bi-clock" aria-hidden="true"></i>{{ $r->submitted_at?->diffForHumans() }}</span>
                        </span>
                    </div>
                    @if ($r->canPreview())
                        <a class="dash-btn is-soft is-sm" href="{{ route('provider.requirements.file', ['requirement' => $r->id, 'inline' => 1]) }}" target="_blank" rel="noopener" data-file-preview="{{ route('provider.requirements.file', ['requirement' => $r->id, 'inline' => 1]) }}" data-file-download="{{ route('provider.requirements.file', $r->id) }}" data-file-name="{{ $r->original_name }}" data-file-type="{{ $r->mime === 'application/pdf' ? 'pdf' : 'image' }}" data-file-meta="{{ $r->scholar->displayName() }} · {{ $r->name }}"><i class="bi bi-eye" aria-hidden="true"></i>Preview</a>
                    @endif
                </li>
                @if ($loop->last)</ul>@endif
            @empty
                <p class="dash-empty"><i class="bi bi-check2-circle" aria-hidden="true"></i>Nothing waiting for review.</p>
            @endforelse
            @if ($awaitingReview->isNotEmpty())<a class="dash-btn is-sm pv-mt" href="{{ route('provider.requirements') }}">Open review queue</a>@endif
        </section>
        @else
        <section class="dash-card">
            <h3 class="dash-card-title"><i class="bi bi-file-earmark-spreadsheet" aria-hidden="true"></i>Import your list</h3>
            <p class="dash-empty"><i class="bi bi-upload" aria-hidden="true"></i>Add many official scholars at once from an Excel file. Each one gets their own BAKR login.</p>
            <a class="dash-btn is-sm pv-mt" href="{{ route('provider.scholars.create') }}#import">Import from Excel</a>
        </section>
        @endif
    </div>
@endif
@endsection
