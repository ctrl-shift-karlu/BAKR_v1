@extends('provider.layout')

{{-- Provider › Messages (owner, 2026-11-20): correction requests from YOUR scholars, sent from their
     Profile ("Request a correction"). Only your own scholars' messages (ProviderScope::messages());
     they never go to the admin. Reply (saved + e-mailed; the scholar also sees it on their Profile),
     then the message is handled. "Open the profile" goes straight to the card to correct (?edit=). --}}

@section('title', 'Messages')
@section('photo', 'opt/lib.jpg')
@section('kicker', 'Messages')
@section('heading')Messages from <em>your scholars</em>@endsection
@section('sub', 'Correction requests your scholars sent from their profile. Fix the details on their profile, reply, and the request is handled.')

@section('content')
@php
    // The profile cards a provider can open for editing (ProviderScholarController::SECTIONS).
    $editable = array_keys(\App\Http\Controllers\Provider\ProviderScholarController::SECTIONS);
@endphp

<div class="dash-section-head">
    <nav class="dash-seg" aria-label="Show">
        <a href="{{ route('provider.messages') }}" @if ($show === 'open') aria-current="true" @endif>Open ({{ $openCount }})</a>
        <a href="{{ route('provider.messages', ['show' => 'all']) }}" @if ($show === 'all') aria-current="true" @endif>All</a>
    </nav>
</div>

@forelse ($messages as $m)
    @php
        $sc = $m->scholar;
        $profileUrl = in_array($m->topic, $editable, true)
            ? route('provider.scholars.show', [$sc->id, 'edit' => $m->topic]).'#'.$m->topic
            : route('provider.scholars.show', $sc->id);
        $replyOpen = session('reply_for') === $m->id || ($errors->has('reply') && (int) old('reply_for') === $m->id);
    @endphp
    <article class="dash-card {{ $loop->first ? '' : 'pv-gap' }}" id="message-{{ $m->id }}" aria-labelledby="msg-{{ $m->id }}">
        <div class="am-head">
            <h2 class="dash-card-title" id="msg-{{ $m->id }}"><i class="bi bi-chat-left-text" aria-hidden="true"></i>{{ $m->topicShort() }}</h2>
            @if ($m->handled_at)<span class="dash-status is-ok">Handled</span>@else<span class="dash-status is-info">Open</span>@endif
        </div>
        <div class="dash-meta">
            <a href="{{ route('provider.scholars.show', $sc->id) }}"><i class="bi bi-person" aria-hidden="true"></i>{{ $sc->displayName() }}</a>
            <span><i class="bi bi-award" aria-hidden="true"></i>{{ $sc->scholarship->shortName() }}</span>
            @if ($sc->scholar_code)<span><i class="bi bi-person-vcard" aria-hidden="true"></i>{{ $sc->scholar_code }}</span>@endif
            @if ($sc->contactEmail())<a href="mailto:{{ $sc->contactEmail() }}"><i class="bi bi-envelope" aria-hidden="true"></i>{{ $sc->contactEmail() }}</a>@endif
            <span><i class="bi bi-calendar3" aria-hidden="true"></i>{{ $m->created_at?->format('M j, Y g:i A') }}</span>
        </div>
        <p class="pv-msg">{{ $m->message }}</p>

        @if ($m->reply)
            <div class="am-sent" aria-label="Your reply">
                <p class="am-sent-title"><i class="bi bi-reply-fill" aria-hidden="true"></i>You replied {{ $m->replied_at?->format('M j, Y g:i A') }}@unless ($m->reply_emailed_at) (not e-mailed)@endunless</p>
                <p class="pv-msg">{{ $m->reply }}</p>
            </div>
        @endif

        <details class="am-reply pv-gap" @if ($replyOpen) open @endif>
            <summary class="dash-btn is-soft is-sm"><i class="bi bi-reply" aria-hidden="true"></i>{{ $m->reply ? 'Reply again' : 'Reply' }}</summary>
            <form method="POST" action="{{ route('provider.messages.reply', $m->id) }}" class="am-reply-form" data-busy="Sending your reply…" data-busy-sub="E-mailing {{ $sc->displayName() }}">
                @csrf
                <input type="hidden" name="reply_for" value="{{ $m->id }}">
                <div class="dash-field">
                    <label for="reply-{{ $m->id }}">Your reply to {{ $sc->displayName() }}@if ($sc->contactEmail()) <span class="pv-muted">({{ $sc->contactEmail() }})</span>@endif</label>
                    <textarea id="reply-{{ $m->id }}" name="reply" rows="5" minlength="10" maxlength="4000" required>{{ $replyOpen ? old('reply') : '' }}</textarea>
                    @if ($replyOpen && $errors->has('reply'))
                        <p class="dash-error">{{ $errors->first('reply') }}</p>
                    @endif
                    <small>Saved here, shown on the scholar's Profile and e-mailed to them with their message quoted underneath. The message is marked handled.</small>
                </div>
                <div><button type="submit" class="dash-btn is-sm"><i class="bi bi-send" aria-hidden="true"></i>Send reply</button></div>
            </form>
        </details>

        <div class="dash-inline-form pv-gap">
            <a class="dash-btn is-soft is-sm" href="{{ $profileUrl }}"><i class="bi bi-pencil-square" aria-hidden="true"></i>Open the profile to correct it</a>
            <form method="POST" action="{{ route('provider.messages.update', $m->id) }}">
                @csrf
                @method('PATCH')
                <input type="hidden" name="action" value="{{ $m->handled_at ? 'reopen' : 'handled' }}">
                <button type="submit" class="dash-btn is-sm {{ $m->handled_at ? 'is-soft' : '' }}"><i class="bi {{ $m->handled_at ? 'bi-arrow-counterclockwise' : 'bi-check2' }}" aria-hidden="true"></i>{{ $m->handled_at ? 'Reopen' : 'Mark handled' }}</button>
            </form>
        </div>
    </article>
@empty
    <div class="dash-card"><p class="dash-empty"><i class="bi bi-inbox" aria-hidden="true"></i>{{ $show === 'open' ? 'No open messages. When one of your scholars asks to correct their details, it shows here.' : 'No messages from your scholars yet.' }}</p></div>
@endforelse

{{ $messages->links('pagination::bootstrap-5') }}
@endsection
