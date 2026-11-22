@extends('admin.layout')

@section('title', 'Accounts')
@section('photo', 'opt/dnsc_gate.jpg')
@section('heading')<em>Accounts</em>@endsection
@section('sub', 'Student, scholar and provider accounts. Deactivating blocks sign-in without deleting anything. Passwords and matching answers are never shown.')

@section('content')
@php
    $roles = \App\Http\Controllers\Admin\AdminController::ROLES;
    $filtered = $q !== '' || $scholarshipId || $campus;
@endphp

<div class="dash-section-head ac-head">
    <nav class="dash-seg" aria-label="Account type">
        @foreach ($roles as $key => $label)
            <a href="{{ route('admin.accounts', ['role' => $key]) }}" @if ($role === $key) aria-current="true" @endif>{{ $label }} ({{ (int) ($counts[$key] ?? 0) }})</a>
        @endforeach
    </nav>
</div>

{{-- Search (button right beside the box from 576px; under it on small phones), then the Scholars-only
     filters and the name order. The selects apply as soon as they change; <noscript> keeps an Apply button. --}}
<form class="ac-filters" method="GET" action="{{ route('admin.accounts') }}" role="search" aria-label="Find {{ strtolower($roles[$role]) }}">
    <input type="hidden" name="role" value="{{ $role }}">
    <div class="dash-field ac-search">
        <label for="q">Search</label>
        <div class="ac-search-row">
            <input id="q" name="q" type="search" value="{{ $q }}" placeholder="Name or e-mail" maxlength="100">
            <button type="submit" class="dash-btn is-soft"><i class="bi bi-search" aria-hidden="true"></i>Search</button>
        </div>
    </div>
    @if ($role === 'scholar')
        <div class="dash-field">
            <label for="f-scholarship">Scholarship</label>
            <select id="f-scholarship" name="scholarship" data-autosubmit>
                <option value="">All scholarships</option>
                @foreach ($scholarships as $s)
                    <option value="{{ $s->id }}" @selected($scholarshipId === $s->id)>{{ $s->shortName() }}: {{ $s->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="dash-field">
            <label for="f-campus">Campus</label>
            <select id="f-campus" name="campus" data-autosubmit>
                <option value="">All campuses</option>
                @foreach ($campuses as $c)
                    <option value="{{ $c }}" @selected($campus === $c)>{{ $c }}</option>
                @endforeach
            </select>
        </div>
    @endif
    <div class="dash-field ac-sort">
        <label for="f-sort">Sort</label>
        <select id="f-sort" name="sort" data-autosubmit>
            @foreach (\App\Http\Controllers\Admin\AdminController::NAME_SORTS as $key => $label)
                <option value="{{ $key }}" @selected($sort === $key)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <noscript><button type="submit" class="dash-btn">Apply</button></noscript>
    @if ($filtered)
        <a class="dash-btn is-soft ac-clear" href="{{ route('admin.accounts', ['role' => $role, 'sort' => $sort === 'name_asc' ? null : $sort]) }}"><i class="bi bi-x-lg" aria-hidden="true"></i>Clear filters</a>
    @endif
</form>

<div class="dash-table-wrap">
    <table class="dash-table">
        <caption class="visually-hidden">{{ $roles[$role] }}</caption>
        <thead>
            <tr>
                <th scope="col">Name</th>
                <th scope="col">E-mail</th>
                <th scope="col">{{ $role === 'scholar' ? 'Scholarships' : ($role === 'provider' ? 'Provider' : 'Joined') }}</th>
                <th scope="col">Status</th>
                <th scope="col">Actions</th>
            </tr>
        </thead>
        <tbody>
        @forelse ($users as $u)
            <tr>
                <td><strong>{{ $u->name }}</strong></td>
                <td>{{ $u->email }}</td>
                <td>
                    @if ($role === 'scholar')
                        {{ $u->scholars->map(fn ($s) => $s->scholarship?->shortName())->filter()->implode(', ') ?: '—' }}
                        @php $campusNames = $u->scholars->map(fn ($s) => $s->campusLabel())->filter()->unique(); @endphp
                        <br><small class="pv-muted">{{ $campusNames->implode(', ') ?: 'Campus not set' }}</small>
                        @if ($u->must_change_password)<br><small class="pv-muted">Has not changed the temporary password yet</small>@endif
                    @elseif ($role === 'provider')
                        {{ config('providers.accounts.'.$u->provider_key.'.name', $u->provider_key) }}
                    @else
                        {{ $u->created_at?->format('M j, Y') }}
                    @endif
                </td>
                <td><span class="dash-pill {{ $u->status === 'active' ? 'is-ok' : 'is-danger' }}">{{ $u->status === 'active' ? 'Active' : 'Deactivated' }}</span></td>
                <td>
                    <div class="dash-inline-form">
                        @if ($u->status === 'active')
                            <form method="POST" action="{{ route('admin.accounts.reset', $u->id) }}" data-busy="Sending the reset link…" data-busy-sub="E-mailing {{ $u->email }}">
                                @csrf
                                <button type="submit" class="dash-btn is-soft is-sm"><i class="bi bi-envelope" aria-hidden="true"></i>Send reset link</button>
                            </form>
                        @endif
                        <form method="POST" action="{{ route('admin.accounts.status', $u->id) }}" @if ($u->status === 'active') data-confirm="Deactivate {{ $u->name }}? They will not be able to sign in until you reactivate the account." @endif>
                            @csrf
                            @method('PATCH')
                            @if ($u->status === 'active')
                                <input type="hidden" name="status" value="inactive">
                                <button type="submit" class="dash-btn is-danger-soft is-sm"><i class="bi bi-person-slash" aria-hidden="true"></i>Deactivate</button>
                            @else
                                <input type="hidden" name="status" value="active">
                                <button type="submit" class="dash-btn is-sm"><i class="bi bi-person-check" aria-hidden="true"></i>Reactivate</button>
                            @endif
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="5"><div class="dash-empty"><i class="bi bi-people" aria-hidden="true"></i>No {{ strtolower($roles[$role]) }} {{ $filtered ? 'match these filters' : 'yet' }}.</div></td></tr>
        @endforelse
        </tbody>
    </table>
</div>
{{ $users->links('pagination::bootstrap-5') }}

@push('scripts')
    <script src="{{ asset('js/bakr-admin-accounts.js') }}" defer></script>
@endpush

<p class="pv-muted pv-gap">
    @if ($role === 'scholar')
        Scholar accounts are created by providers when they add a scholar. Their details are managed by that provider.
    @elseif ($role === 'provider')
        Provider accounts are created by contacting BAKR.
    @else
        Students create their own accounts to use matching.
    @endif
</p>
@endsection
