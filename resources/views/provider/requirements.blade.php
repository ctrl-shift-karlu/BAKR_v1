@extends('provider.layout')

@section('title', 'Requirements')
@section('photo', 'opt/lib.jpg')
@section('kicker', $term)
@section('heading')Scholar <em>requirements</em>@endsection
@section('sub', 'Maintaining documents per semester: each scholarship\'s deadline, what each scholar has uploaded, and what still needs review.')

@section('content')
<form class="dash-filters" method="GET" action="{{ route('provider.requirements') }}">
    <div class="dash-field">
        <label for="term">Semester</label>
        <select id="term" name="term" data-autosubmit>
            @foreach ($terms as $t)<option value="{{ $t }}" @selected($t === $term)>{{ $t }}</option>@endforeach
        </select>
    </div>
    <div class="dash-field">
        <label for="f-scholarship">Scholarship</label>
        <select id="f-scholarship" name="scholarship" data-autosubmit>
            <option value="">All scholarships</option>
            @foreach ($scholarships as $s)
                <option value="{{ $s->id }}" @selected($scholarship?->id === $s->id)>{{ $s->shortName() }} — {{ $s->name }}</option>
            @endforeach
        </select>
    </div>
    <noscript><button type="submit" class="dash-btn is-soft">Show</button></noscript>
</form>

<section class="dash-kpis" aria-label="Requirements for {{ $term }}">
    @foreach (['approved' => ['is-1', 'bi-check2-all'], 'submitted' => ['is-2', 'bi-inbox'], 'needs_revision' => ['is-3', 'bi-arrow-repeat'], 'missing' => ['is-4', 'bi-hourglass']] as $key => [$tone, $icon])
        <div class="dash-kpi {{ $tone }}">
            <span class="dash-kpi-icon"><i class="bi {{ $icon }}" aria-hidden="true"></i></span>
            <span><strong>{{ (int) ($summary[$key] ?? 0) }}</strong><span>{{ \App\Models\ScholarRequirement::STATUSES[$key] }}</span></span>
        </div>
    @endforeach
</section>

{{-- Each scholarship's OWN deadline for this semester (owner, 2026-11-21). Saving one opens the semester's
     documents for that scholarship's scholars (those who started by this semester) and e-mails them the date. --}}
<section class="dash-card" id="deadlines" aria-labelledby="deadlines-title">
    <h3 class="dash-card-title" id="deadlines-title"><i class="bi bi-calendar-event" aria-hidden="true"></i>Deadlines for {{ $term }}</h3>
    <p class="pv-muted">Each scholarship has its own deadline for each semester. Saving one opens the semester's maintaining documents for that scholarship's active scholars who started by this semester, and e-mails them the date. Change it any time; they are told again. After the deadline, a scholar with a document still not submitted shows as <b>Inactive</b>.</p>
    @if ($overdue)<p class="pv-attn-danger"><i class="bi bi-alarm" aria-hidden="true"></i>{{ $overdue }} {{ \Illuminate\Support\Str::plural('document', $overdue) }} past due.</p>@endif
    @error('due_on')<p class="dash-error" role="alert">{{ $message }}</p>@enderror

    <ul class="pv-deadlines">
        @foreach ($listed as $s)
            @php
                $d = $deadlines->get($s->id);
                $hasDocs = (bool) $s->maintainingDocuments();
                $n = (int) ($owing[$s->id] ?? 0);
            @endphp
            <li class="pv-deadline">
                <div class="pv-deadline-who">
                    <strong>{{ $s->shortName() }} <small>{{ $s->name }}</small></strong>
                    <span class="dash-meta">
                        @if ($d)
                            <span class="dash-status {{ $d->due_on->isPast() && ! $d->due_on->isToday() ? 'is-warn' : 'is-ok' }}">Due {{ $d->due_on->format('M j, Y') }}</span>
                        @else
                            <span class="dash-status">Deadline not set</span>
                        @endif
                        <span><i class="bi bi-people" aria-hidden="true"></i>{{ $n }} {{ \Illuminate\Support\Str::plural('scholar', $n) }}</span>
                    </span>
                </div>
                @if ($hasDocs)
                    <form method="POST" action="{{ route('provider.requirements.deadline') }}" class="pv-deadline-form" data-busy="Saving the deadline…" data-busy-sub="Opening the documents and e-mailing your scholars">
                        @csrf
                        <input type="hidden" name="scholarship_id" value="{{ $s->id }}">
                        <input type="hidden" name="term" value="{{ $term }}">
                        <div class="dash-field">
                            <label for="due-{{ $s->id }}">Deadline</label>
                            <input id="due-{{ $s->id }}" name="due_on" type="date" required value="{{ $d?->due_on->toDateString() ?? $suggested?->toDateString() }}">
                        </div>
                        <button type="submit" class="dash-btn is-sun is-sm"><i class="bi bi-calendar-check" aria-hidden="true"></i>{{ $d ? 'Save deadline' : 'Set deadline' }}</button>
                    </form>
                @else
                    <p class="pv-muted pv-deadline-note">No maintaining documents yet. <a href="{{ route('provider.scholarships', ['s' => $s->id]) }}#maintaining">Add them under My scholarships</a>.</p>
                @endif
            </li>
        @endforeach
    </ul>
</section>

<div class="dash-split pv-gap">
    <section class="dash-card" aria-labelledby="by-scholar">
        <h3 class="dash-card-title" id="by-scholar"><i class="bi bi-people" aria-hidden="true"></i>By scholar</h3>
        @forelse ($scholars as $sc)
            @php
                $owed = $sc->requirements->filter->isOwed();   // "only if it applies" items count once sent
                $count = $owed->count();
                $done = $owed->where('status', 'approved')->count();
                $toReview = $sc->requirements->where('status', 'submitted')->count();
                $late = $sc->requirements->filter->isOverdue()->count();
            @endphp
            {{-- Accordion row: opens by itself when something waits for review. --}}
            <details class="pv-acc" @if ($toReview) open @endif>
                <summary>
                    <span class="dash-avatar is-1" aria-hidden="true">{{ $sc->initials() }}</span>
                    <span class="pv-acc-who">
                        <strong>{{ $sc->displayName() }}</strong>
                        <span class="dash-meta">
                            <span><i class="bi bi-award" aria-hidden="true"></i>{{ $sc->scholarship->shortName() }}</span>
                            @if ($sc->scholar_code)<span><i class="bi bi-person-vcard" aria-hidden="true"></i>{{ $sc->scholar_code }}</span>@endif
                            <span><i class="bi bi-check2-square" aria-hidden="true"></i>{{ $done }}/{{ $count }} approved</span>
                        </span>
                    </span>
                    <span class="pv-acc-flags dash-status-group">
                        @if ($toReview)<span class="dash-status is-info">{{ $toReview }} to review</span>@endif
                        @if ($late)<span class="dash-status is-danger">{{ $late }} past due</span>@endif
                        @if ($done === $count && $count)<span class="dash-status is-ok">Complete</span>@endif
                    </span>
                    <span class="dash-progress pv-acc-bar" aria-hidden="true"><i data-w="{{ $count ? round($done / $count * 100) : 0 }}"></i></span>
                    <i class="bi bi-chevron-down pv-acc-chev" aria-hidden="true"></i>
                </summary>
                <div class="pv-acc-body">
                    <ul class="pv-reqs">
                        @foreach ($sc->requirements as $r)
                            @include('provider.partials.requirement-item', ['r' => $r->setRelation('scholar', $sc)])
                        @endforeach
                    </ul>
                    <a class="dash-btn is-soft is-sm pv-acc-link" href="{{ route('provider.scholars.show', $sc->id) }}"><i class="bi bi-person-lines-fill" aria-hidden="true"></i>View {{ $sc->displayName() }}'s profile</a>
                </div>
            </details>
        @empty
            <p class="dash-empty">No documents for {{ $term }} yet. Set a deadline above to open them.</p>
        @endforelse
        {{ $scholars->links('pagination::bootstrap-5') }}
    </section>

    <section class="dash-card" aria-labelledby="waiting-title">
        <h3 class="dash-card-title" id="waiting-title"><i class="bi bi-inbox" aria-hidden="true"></i>Waiting for review <span class="dash-pill is-info">{{ $waiting->count() }}</span></h3>
        @forelse ($waiting as $r)
            @if ($loop->first)<ul class="pv-reqs">@endif
            @include('provider.partials.requirement-item', ['r' => $r, 'who' => $r->scholar->displayName(), 'whoScholarship' => $r->scholar->scholarship->shortName()])
            @if ($loop->last)</ul>@endif
        @empty
            <p class="dash-empty"><i class="bi bi-check2-circle" aria-hidden="true"></i>All caught up.</p>
        @endforelse
    </section>
</div>
@endsection
