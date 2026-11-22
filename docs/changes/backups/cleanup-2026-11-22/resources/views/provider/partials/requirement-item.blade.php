{{-- One maintaining document ($r) in a provider list: status, due date, file (Preview + Download),
     the provider's note and the review actions. $who / $whoScholarship: optional scholar name and
     scholarship code (review queue). Details = .dash-meta, status = .dash-status (no "·" + pill).
     Preview opens the shared #pv-file-preview dialog (provider.layout); without JavaScript the
     Preview link opens the file in a new tab. Download always stays available. --}}
@php
    $reqPill = ['approved' => 'is-ok', 'submitted' => 'is-info', 'needs_revision' => 'is-warn', 'missing' => ''];
    $fileUrl = route('provider.requirements.file', $r->id);
@endphp
<li class="pv-req @if ($r->isOverdue()) is-overdue @endif">
    <div class="pv-req-main">
        <strong>{{ $who ?? $r->name }}</strong>
        <span class="pv-req-pills dash-status-group">
            @if ($r->isOverdue())<span class="dash-status is-danger">Past due</span>@endif
            <span class="dash-status {{ $reqPill[$r->status] ?? '' }}">{{ $r->statusLabel() }}</span>
        </span>
    </div>
    <div class="pv-req-meta dash-meta">
        @isset($who)<span><i class="bi bi-file-earmark-text" aria-hidden="true"></i>{{ $r->name }}</span>@endisset
        @isset($whoScholarship)<span><i class="bi bi-award" aria-hidden="true"></i>{{ $whoScholarship }}</span>@endisset
        @if ($r->due_on)<span class="{{ $r->isOverdue() ? 'is-attn' : '' }}"><i class="bi bi-calendar-event" aria-hidden="true"></i>Due {{ $r->due_on->format('M j, Y') }}</span>@endif
        @if ($r->hasFile())
            <span><i class="bi bi-upload" aria-hidden="true"></i>Uploaded {{ $r->submitted_at?->format('M j, Y') }}</span>
        @else
            <span>No file yet</span>
        @endif
        @if ($r->is_optional)<span title="Only scholars it applies to submit this; it never makes anyone Inactive"><i class="bi bi-question-circle" aria-hidden="true"></i>Only if it applies</span>@endif
        @if ($r->review_note)<span class="pv-note">“{{ $r->review_note }}”</span>@endif
    </div>
    @if ($r->hasFile())
        <div class="pv-file">
            <i class="bi {{ $r->mime === 'application/pdf' ? 'bi-file-earmark-pdf' : 'bi-file-earmark-image' }}" aria-hidden="true"></i>
            <span class="pv-file-name" title="{{ $r->original_name }}">{{ $r->original_name }}</span>
            <span class="pv-file-actions">
                @if ($r->canPreview())
                    <a class="dash-btn is-soft is-sm" href="{{ $fileUrl }}?inline=1" target="_blank" rel="noopener"
                       data-file-preview="{{ $fileUrl }}?inline=1" data-file-download="{{ $fileUrl }}" data-file-name="{{ $r->original_name }}"
                       data-file-type="{{ $r->mime === 'application/pdf' ? 'pdf' : 'image' }}"
                       data-file-meta="{{ $r->scholar?->displayName() }} · {{ $r->name }}"><i class="bi bi-eye" aria-hidden="true"></i>Preview</a>
                @endif
                <a class="dash-btn is-soft is-sm" href="{{ $fileUrl }}" download><i class="bi bi-download" aria-hidden="true"></i>Download</a>
            </span>
        </div>
    @endif
    @include('provider.partials.review-form', ['r' => $r])
</li>
