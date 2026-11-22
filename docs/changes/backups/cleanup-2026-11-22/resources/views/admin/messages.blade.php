@extends('admin.layout')

@section('title', 'Messages')
@section('photo', 'opt/lib.jpg')
@section('heading')<em>Messages</em>@endsection
@section('sub', 'Messages from the Contact page and the portal Help pages. Reply by e-mail, then mark them handled.')

@section('content')
<div class="dash-section-head">
    <nav class="dash-seg" aria-label="Show">
        <a href="{{ route('admin.messages') }}" @if ($show === 'open') aria-current="true" @endif>Open ({{ $openCount }})</a>
        <a href="{{ route('admin.messages', ['show' => 'all']) }}" @if ($show === 'all') aria-current="true" @endif>All</a>
    </nav>
</div>

@forelse ($messages as $m)
    <article class="dash-card {{ $loop->first ? '' : 'pv-gap' }}" aria-labelledby="msg-{{ $m->id }}">
        <div class="am-head">
            <h2 class="dash-card-title" id="msg-{{ $m->id }}"><i class="bi bi-chat-left-text" aria-hidden="true"></i>{{ $m->topicLabel() }}</h2>
            <span class="dash-status-group">
                @if ($m->handled_at)<span class="dash-status is-ok">Handled</span>@else<span class="dash-status is-info">Open</span>@endif
                @if ($m->approved_at)<span class="dash-status is-info">Shown publicly</span>@endif
            </span>
        </div>
        <div class="dash-meta">
            <span><i class="bi bi-person" aria-hidden="true"></i>{{ $m->name }}</span>
            <a href="mailto:{{ $m->email }}"><i class="bi bi-envelope" aria-hidden="true"></i>{{ $m->email }}</a>
            <span><i class="bi bi-calendar3" aria-hidden="true"></i>{{ $m->created_at?->format('M j, Y g:i A') }}</span>
            @if ($m->rating)<span><i class="bi bi-star" aria-hidden="true"></i>Rating {{ $m->rating }}/5</span>@endif
            @if ($m->aspectLabels())<span><i class="bi bi-ui-checks" aria-hidden="true"></i>Used: {{ implode(', ', $m->aspectLabels()) }}</span>@endif
            @if (! $m->emailed_at)<span class="is-attn"><i class="bi bi-exclamation-triangle" aria-hidden="true"></i>E-mail notice failed</span>@endif
        </div>
        @if (filled($m->message))
            <p class="pv-msg">{{ $m->message }}</p>
        @else
            <p class="pv-msg pv-muted">Rating only, no comment.</p>
        @endif

        @if ($m->reply)
            <div class="am-sent" aria-label="Your reply">
                <p class="am-sent-title"><i class="bi bi-reply-fill" aria-hidden="true"></i>You replied {{ $m->replied_at?->format('M j, Y g:i A') }}</p>
                <p class="pv-msg">{{ $m->reply }}</p>
            </div>
        @endif

        {{-- Reply by e-mail: sent from BAKR to the address the sender gave (no mail program needed on this computer). --}}
        @php $replyOpen = session('reply_for') === $m->id || $errors->has('reply') && old('reply_for') == $m->id; @endphp
        <details class="am-reply pv-gap" @if ($replyOpen) open @endif>
            <summary class="dash-btn is-soft is-sm"><i class="bi bi-reply" aria-hidden="true"></i>{{ $m->reply ? 'Reply again' : 'Reply by e-mail' }}</summary>
            <form method="POST" action="{{ route('admin.messages.reply', $m->id) }}" class="am-reply-form" data-busy="Sending your reply…" data-busy-sub="E-mailing {{ $m->name }}">
                @csrf
                <input type="hidden" name="reply_for" value="{{ $m->id }}">
                <div class="dash-field">
                    <label for="reply-{{ $m->id }}">Your reply to {{ $m->name }} <span class="pv-muted">({{ $m->email }})</span></label>
                    <textarea id="reply-{{ $m->id }}" name="reply" rows="5" minlength="10" maxlength="4000" required>{{ $replyOpen ? old('reply') : '' }}</textarea>
                    @if ($replyOpen && $errors->has('reply'))
                        <p class="dash-error">{{ $errors->first('reply') }}</p>
                    @endif
                    <small>Sent from BAKR with their message quoted underneath. Replies to it come back to {{ config('bakr.contact_to') }}. The message is marked handled.</small>
                </div>
                <div><button type="submit" class="dash-btn is-sm"><i class="bi bi-send" aria-hidden="true"></i>Send reply</button></div>
            </form>
        </details>

        <div class="dash-inline-form pv-gap">
            <form method="POST" action="{{ route('admin.messages.update', $m->id) }}">
                @csrf
                @method('PATCH')
                <input type="hidden" name="action" value="{{ $m->handled_at ? 'reopen' : 'handled' }}">
                <button type="submit" class="dash-btn is-sm {{ $m->handled_at ? 'is-soft' : '' }}"><i class="bi {{ $m->handled_at ? 'bi-arrow-counterclockwise' : 'bi-check2' }}" aria-hidden="true"></i>{{ $m->handled_at ? 'Reopen' : 'Mark handled' }}</button>
            </form>
            @if ($m->topic === 'feedback' && $m->share_publicly)
                <form method="POST" action="{{ route('admin.messages.update', $m->id) }}">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="action" value="{{ $m->approved_at ? 'hide' : 'approve' }}">
                    <button type="submit" class="dash-btn is-soft is-sm"><i class="bi {{ $m->approved_at ? 'bi-eye-slash' : 'bi-megaphone' }}" aria-hidden="true"></i>{{ $m->approved_at ? 'Hide from public site' : 'Show on public site' }}</button>
                </form>
            @endif
        </div>
    </article>
@empty
    <div class="dash-card"><p class="dash-empty"><i class="bi bi-inbox" aria-hidden="true"></i>{{ $show === 'open' ? 'No open messages.' : 'No messages yet.' }}</p></div>
@endforelse

{{ $messages->links('pagination::bootstrap-5') }}
@endsection
