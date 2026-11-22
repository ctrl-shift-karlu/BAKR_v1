@extends('provider.layout')

@section('title', 'Scholars')
@section('photo', 'opt/student.jpg')
@section('kicker', 'Scholars')
@section('heading')Your <em>scholars</em>@endsection
@section('sub', $scope->has('disbursements') || $scope->has('requirements') ? 'Everyone holding one of your scholarships, with this semester\'s stipend and documents.' : 'Everyone holding one of your scholarships.')
@section('actions')
    <a class="dash-btn is-sun" href="{{ route('provider.scholars.create') }}"><i class="bi bi-person-plus" aria-hidden="true"></i>Add new scholar</a>
    <a class="dash-btn is-soft" href="{{ route('provider.scholars.create') }}#import"><i class="bi bi-file-earmark-spreadsheet" aria-hidden="true"></i>Import from Excel</a>
@endsection

@section('content')
@php
    $statusIcon = ['active' => 'bi-check2-circle', 'inactive' => 'bi-hourglass-bottom', 'terminated' => 'bi-x-circle'];
    $statusHelp = ['active' => 'Up to date', 'inactive' => $scope->has('requirements') ? 'Maintaining documents past due' : 'Marked inactive by you', 'terminated' => 'Ended by you'];
    $payPill = ['released' => 'is-ok', 'pending' => 'is-warn'];
    $hasPay = $scope->has('disbursements');
    $hasReqs = $scope->has('requirements');
    $cols = 5 + ($hasPay ? 2 : 0) + ($hasReqs ? 1 : 0);
@endphp

<p class="pv-muted pv-list-hint"><i class="bi bi-info-circle" aria-hidden="true"></i><span>
    @if ($hasReqs)
        Inactive is automatic (a required document past its due date). Use <strong>Terminate</strong> to end a scholarship, <strong>Reinstate</strong> to undo it.
    @else
        Maintaining documents are checked in person at the official DNSC scholarship office. Use <strong>Mark inactive</strong> when a scholar's documents are not complete, <strong>Terminate</strong> to end a scholarship.
    @endif
</span></p>

<section class="dash-kpis pv-kpis-sm" aria-label="Scholars by status">
    @foreach (\App\Models\Scholar::STATUSES as $key => $label)
        <a class="dash-kpi is-{{ $loop->iteration }}" href="{{ route('provider.scholars', array_filter(['status' => $key, 'scholarship' => $filters['scholarship']])) }}" @if ($filters['status'] === $key) aria-current="true" @endif title="{{ $statusHelp[$key] }}">
            <span class="dash-kpi-icon"><i class="bi {{ $statusIcon[$key] }}" aria-hidden="true"></i></span>
            <span><strong>{{ $statusCounts[$key] ?? 0 }}</strong><span>{{ $label }}</span></span>
        </a>
    @endforeach
</section>

<form class="dash-filters pv-gap" method="GET" action="{{ route('provider.scholars') }}" role="search">
    <div class="dash-field is-grow">
        <label for="q">Search</label>
        <input id="q" name="q" value="{{ $filters['q'] }}" placeholder="Name, Scholar ID, student number or e-mail">
    </div>
    <div class="dash-field">
        <label for="f-scholar-id">Scholar ID starts with</label>
        <input id="f-scholar-id" name="scholar_id" class="pv-code-input" maxlength="40" autocomplete="off" spellcheck="false" value="{{ $filters['scholar_id'] }}" placeholder="e.g. SCID-2026" title="Shows the scholars whose Scholar ID starts with this. Type none for scholars without one.">
    </div>
    <div class="dash-field">
        <label for="f-scholarship">Scholarship</label>
        <select id="f-scholarship" name="scholarship">
            <option value="">All scholarships</option>
            @foreach ($scholarships as $s)
                <option value="{{ $s->id }}" @selected($filters['scholarship'] == $s->id)>{{ $s->shortName() }} — {{ $s->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="dash-field">
        <label for="f-status">Status</label>
        <select id="f-status" name="status">
            <option value="">Any status</option>
            @foreach (\App\Models\Scholar::STATUSES as $key => $label)
                <option value="{{ $key }}" @selected($filters['status'] === $key)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="dash-btn"><i class="bi bi-funnel" aria-hidden="true"></i>Filter</button>
    @if (array_filter($filters))<a class="dash-btn is-soft" href="{{ route('provider.scholars') }}">Clear</a>@endif
</form>

<div class="dash-table-wrap">
    <table class="dash-table is-many-cols">
        <thead>
            <tr>
                <th scope="col">Scholar</th><th scope="col">Scholarship</th><th scope="col">Program / year</th><th scope="col">Status</th><th scope="col">Actions</th>
                @if ($hasPay)<th scope="col" class="is-num">Stipend / release</th><th scope="col" title="{{ $term }}">This semester</th>@endif
                @if ($hasReqs)<th scope="col">Requirements</th>@endif
            </tr>
        </thead>
        <tbody>
        @forelse ($scholars as $sc)
            @php($pay = $sc->disbursements->first())
            <tr class="is-clickable">
                <td>
                    <div class="dash-person">
                        <span class="dash-avatar is-1" aria-hidden="true">{{ $sc->initials() }}</span>
                        <a class="is-link" href="{{ route('provider.scholars.show', $sc->id) }}" title="Open {{ $sc->displayName() }}'s full profile"><strong>{{ $sc->displayName() }}</strong><span class="dash-meta">
                            @if ($sc->scholar_code)<span><i class="bi bi-person-vcard" aria-hidden="true"></i><span class="pv-code">{{ $sc->scholar_code }}</span></span>@else<span class="is-attn"><i class="bi bi-person-vcard" aria-hidden="true"></i>No Scholar ID</span>@endif
                            <span>{{ $sc->student_number ?: 'No student number' }}</span>
                            @switch ($sc->accountState())
                                @case('not_sent')<span class="is-attn" title="The login e-mail was not sent. Open the scholar to resend it."><i class="bi bi-envelope-exclamation" aria-hidden="true"></i>Login not sent</span>@break
                                @case('waiting')<span title="Login e-mailed; the scholar has not signed in yet"><i class="bi bi-envelope" aria-hidden="true"></i>Not signed in yet</span>@break
                                @case('none')<span title="Added before accounts were created automatically"><i class="bi bi-person-dash" aria-hidden="true"></i>No account</span>@break
                            @endswitch
                        </span></a>
                    </div>
                </td>
                <td><span class="dash-pill is-violet">{{ $sc->scholarship->shortName() }}</span>@if ($sc->grantLabel())<br><small class="pv-muted">{{ $sc->grantLabel() }}</small>@elseif ($sc->scholarship->hasGrantTypes())<br><small class="pv-attn">Grant type not set</small>@endif</td>
                <td>{{ $sc->program ?: '—' }}@if ($sc->campusLabel() || $sc->year_level)<span class="dash-meta">@if ($sc->campusLabel())<span><i class="bi bi-geo-alt" aria-hidden="true"></i>{{ $sc->campusLabel() }}</span>@endif @if ($sc->year_level)<span><i class="bi bi-mortarboard" aria-hidden="true"></i>{{ $sc->yearLabel() }}</span>@endif</span>@endif</td>
                <td><span class="dash-status {{ $sc->statusTone() }}" @if ($sc->standing() === 'inactive') title="{{ $sc->isMarkedInactive() ? 'Marked inactive by you' : 'A maintaining document is past its due date and not submitted' }}" @endif>{{ $sc->statusLabel() }}</span></td>
                {{-- Status actions (same rules as the scholar profile; the server re-checks them) --}}
                <td>
                    <div class="pv-row-actions">
                        <a class="dash-btn is-soft is-sm" href="{{ route('provider.scholars.show', $sc->id) }}"><i class="bi bi-person-lines-fill" aria-hidden="true"></i>View profile</a>
                        @if ($sc->isTerminated())
                            <form method="POST" action="{{ route('provider.scholars.status', $sc->id) }}">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="active">
                                <button type="submit" class="dash-btn is-soft is-sm"><i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i>Reinstate</button>
                            </form>
                        @else
                            @unless ($hasReqs)
                                <form method="POST" action="{{ route('provider.scholars.status', $sc->id) }}">
                                    @csrf @method('PATCH')
                                    @if ($sc->isMarkedInactive())
                                        <input type="hidden" name="status" value="active">
                                        <button type="submit" class="dash-btn is-sm"><i class="bi bi-check2-circle" aria-hidden="true"></i>Mark active</button>
                                    @else
                                        <input type="hidden" name="status" value="inactive">
                                        <button type="submit" class="dash-btn is-sun is-sm"><i class="bi bi-pause-circle" aria-hidden="true"></i>Mark inactive</button>
                                    @endif
                                </form>
                            @endunless
                            <a class="dash-btn is-danger-soft is-sm" href="{{ route('provider.scholars.show', $sc->id) }}#confirm-terminate"
                               data-terminate-url="{{ route('provider.scholars.status', $sc->id) }}" data-terminate-name="{{ $sc->displayName() }}" data-terminate-scholarship="{{ $sc->scholarship->shortName() }}"><i class="bi bi-x-circle" aria-hidden="true"></i>Terminate</a>
                        @endif
                    </div>
                </td>
                @if ($hasPay)
                <td class="is-num">{{ $sc->stipend_amount !== null ? '₱'.number_format($sc->stipend_amount, 2) : '—' }}</td>
                <td>
                    @if ($pay && $sc->disbursements->count() > 1)
                        @php($paid = $sc->disbursements->where('status', 'released')->count())
                        <span class="dash-status {{ $paid === $sc->disbursements->count() ? 'is-ok' : 'is-warn' }}">{{ $paid }}/{{ $sc->disbursements->count() }} months released</span>
                    @elseif ($pay)
                        <span class="dash-status {{ $payPill[$pay->status] ?? '' }}">{{ $pay->statusLabel() }}</span>
                    @else
                        <small class="pv-muted">Not prepared</small>
                    @endif
                </td>
                @endif
                @if ($hasReqs)
                <td class="pv-req-cell">
                    @if ($sc->reqs_total)
                        <div class="dash-progress" aria-label="{{ $sc->reqs_approved }} of {{ $sc->reqs_total }} approved"><i data-w="{{ round($sc->reqs_approved / $sc->reqs_total * 100) }}"></i></div>
                        <span class="dash-meta"><span>{{ $sc->reqs_approved }}/{{ $sc->reqs_total }} approved</span>@if ($sc->reqs_waiting)<span class="is-attn">{{ $sc->reqs_waiting }} to review</span>@endif</span>
                    @else
                        <small class="pv-muted">No checklist yet</small>
                    @endif
                </td>
                @endif            </tr>
        @empty
            <tr><td colspan="{{ $cols }}"><div class="dash-empty"><i class="bi bi-mortarboard" aria-hidden="true"></i>No scholars match these filters. <a href="{{ route('provider.scholars.create') }}">Add your official scholars</a>.</div></td></tr>
        @endforelse
        </tbody>
    </table>
</div>
{{ $scholars->links('pagination::bootstrap-5') }}

{{-- One red confirmation for every "Terminate" in the list (bakr-provider.js fills in who and where).
     Without JavaScript the Terminate link opens the scholar's profile, which has its own confirmation. --}}
<dialog class="dash-dialog dash-confirm" id="confirm-terminate" aria-labelledby="terminate-title">
    <div class="dash-dialog-body">
        <span class="dash-confirm-icon" aria-hidden="true"><i class="bi bi-x-circle"></i></span>
        <h2 id="terminate-title">Terminate <span data-terminate-who>this scholar</span>?</h2>
        <p>Their <span data-terminate-what>scholarship</span> ends: no more stipends or requirement checklists. You can reinstate them later.</p>
    </div>
    <div class="dash-dialog-foot">
        <button type="button" class="dash-btn is-soft" data-close-dialog>Cancel</button>
        <form method="POST" action="" data-terminate-form>
            @csrf @method('PATCH')
            <input type="hidden" name="status" value="terminated">
            <button type="submit" class="dash-btn is-danger"><i class="bi bi-x-circle" aria-hidden="true"></i>Terminate</button>
        </form>
    </div>
</dialog>
@endsection
