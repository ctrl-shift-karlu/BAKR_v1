{{-- Review actions for one requirement ($r): Approve, or ask for a revision with a note.
     "Ask for revision" is a small disclosure: the note field opens on its own line underneath. --}}
@if ($r->status === 'submitted' || $r->status === 'needs_revision' || ($r->status === 'approved' && $r->hasFile()))
    <div class="pv-review">
        @if ($r->status !== 'approved' && $r->hasFile())
            <form method="POST" action="{{ route('provider.requirements.review', $r->id) }}">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="approved">
                <button type="submit" class="dash-btn is-sm"><i class="bi bi-check2" aria-hidden="true"></i>Approve</button>
            </form>
        @endif
        @if ($r->status !== 'needs_revision')
            <details class="pv-decline">
                <summary class="dash-btn is-soft is-sm"><i class="bi bi-arrow-repeat" aria-hidden="true"></i>Ask for revision</summary>
                <form method="POST" action="{{ route('provider.requirements.review', $r->id) }}" class="pv-decline-form">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="needs_revision">
                    <label class="visually-hidden" for="rn-{{ $r->id }}">What needs to change</label>
                    <input id="rn-{{ $r->id }}" class="dash-input" name="review_note" maxlength="500" required placeholder="What needs to change?">
                    <button type="submit" class="dash-btn is-sun is-sm"><i class="bi bi-send" aria-hidden="true"></i>Send</button>
                </form>
            </details>
        @endif
    </div>
@endif
