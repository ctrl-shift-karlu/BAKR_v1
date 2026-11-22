{{-- Success modal (owner, 2026-11-20): shown once, on the page after something was SENT (a message, a reply,
     an e-mailed reset link or login). Controller::sent() flashes session('sent') = ['title', 'text'].
     Included in the flash area of every portal layout; bakr-dashboard.js opens <dialog data-auto-open> on load.
     Without JavaScript the same text shows as a normal green notice (<noscript>). --}}
@php $sent = session('sent'); @endphp
@if (is_array($sent) && filled($sent['title'] ?? null))
    <dialog class="dash-dialog dash-confirm" id="sent-dialog" aria-labelledby="sent-dialog-title" aria-describedby="sent-dialog-text" data-auto-open>
        <div class="dash-dialog-body">
            <span class="dash-confirm-icon is-ok" aria-hidden="true"><i class="bi bi-check2-circle"></i></span>
            <h2 id="sent-dialog-title">{{ $sent['title'] }}</h2>
            <p id="sent-dialog-text">{{ $sent['text'] ?? '' }}</p>
        </div>
        <div class="dash-dialog-foot">
            <button type="button" class="dash-btn" data-close-dialog autofocus><i class="bi bi-check2" aria-hidden="true"></i>OK</button>
        </div>
    </dialog>
    <noscript>
        <div class="dash-flash" role="status"><i class="bi bi-check-circle-fill" aria-hidden="true"></i><span><strong>{{ $sent['title'] }}.</strong> {{ $sent['text'] ?? '' }}</span></div>
    </noscript>
@endif
