{{-- One term's maintaining documents as accordion rows (scholar portal › Requirements).
     $items: ScholarRequirement collection; $state: status → [label, pill class, icon, note];
     $inlineTypes: mimes the preview can show; $provider: who receives the file.
     The quick action and the "Upload / Replace" buttons open the upload dialog (data-upload-*);
     the <noscript> form posts the same file to the same route without JavaScript. --}}
<ul class="sc-doc-list">
    @foreach ($items as $r)
        @php
            [$label, $pill, $icon, $note] = $state[$r->status] ?? [$r->statusLabel(), '', 'bi-file-earmark', ''];
            // $canUpload (from the page) is false for a terminated scholarship; approved items are final.
            $rowCanUpload = ($canUpload ?? true) && $r->status !== 'approved';
            $action = $r->hasFile() ? 'Replace' : 'Upload';
            $fileUrl = $r->hasFile() ? route('scholar.requirements.file', $r->id) : null;
            $previewable = $r->hasFile() && in_array($r->mime, $inlineTypes, true);
            // This document's own accepted files and size (set by the provider on My scholarships).
            $uploadData = 'data-upload-url="'.e(route('scholar.requirements.upload', $r->id)).'" data-upload-name="'.e($r->name).'" data-upload-provider="'.e($provider).'"'
                .' data-upload-types="'.e(implode(',', \App\Support\MaintainingDocument::extensions($r->acceptedTypes()))).'"'
                .' data-upload-accept="'.e(\App\Support\MaintainingDocument::accept($r->acceptedTypes())).'"'
                .' data-upload-max-kb="'.$r->maxKb().'" data-upload-rules="'.e($r->uploadRulesLabel()).'"'
                .($r->hasFile() ? ' data-upload-current="'.e($r->original_name).'" data-upload-current-date="'.e($r->submitted_at?->format('M j, Y')).'"' : '');
        @endphp
        <li class="sc-doc is-{{ $r->status }}" id="req-{{ $r->id }}">
            <div class="sc-doc-head">
                <button type="button" class="sc-doc-toggle" aria-expanded="false" aria-controls="req-{{ $r->id }}-panel" data-doc-toggle>
                    <span class="sc-doc-icon" aria-hidden="true"><i class="bi {{ $icon }}"></i></span>
                    <span class="sc-doc-title">
                        <strong>{{ $r->name }}</strong>
                        <span class="dash-meta">
                            @if ($r->hasFile())<span><i class="bi bi-send" aria-hidden="true"></i>Sent {{ $r->submitted_at?->format('M j, Y') }}</span>@else<span>{{ $note }}</span>@endif
                            @if ($r->due_on && $r->status !== 'approved')<span class="{{ $r->isOverdue() ? 'is-attn' : '' }}"><i class="bi bi-calendar-event" aria-hidden="true"></i>Due {{ $r->due_on->format('M j, Y') }}</span>@endif
                        </span>
                    </span>
                </button>
                <span class="dash-status-group">
                    @if ($r->isOverdue())<span class="dash-status is-danger">Past due</span>@endif
                    <span class="dash-status {{ $pill }}">{{ $label }}</span>
                </span>
                @if ($rowCanUpload)
                    <button type="button" class="dash-btn is-sm {{ in_array($r->status, ['missing', 'needs_revision'], true) ? '' : 'is-soft' }}" {!! $uploadData !!} aria-label="{{ $action }}: {{ $r->name }}"><i class="bi bi-upload" aria-hidden="true"></i>{{ $action }}</button>
                @endif
                <i class="bi bi-chevron-down sc-doc-chev" aria-hidden="true"></i>
            </div>

            <div class="sc-doc-panel" id="req-{{ $r->id }}-panel">
                <div class="sc-doc-box">
                    <dl class="sc-doc-facts">
                        <div><dt>Status</dt><dd><span class="dash-status {{ $pill }}">{{ $label }}</span> <span class="sc-doc-note">{{ $note }}</span></dd></div>
                        <div><dt>Sent to</dt><dd>{{ $provider }}</dd></div>
                        <div><dt>Semester</dt><dd>{{ $r->term }}</dd></div>
                        <div><dt>Files</dt><dd>{{ $r->uploadRulesLabel() }}@if ($r->is_optional) <span class="sc-doc-note">Only if it applies to you.</span>@endif</dd></div>
                        @if ($r->due_on)<div><dt>Due</dt><dd>{{ $r->due_on->format('M j, Y') }}@if ($r->isOverdue()) <span class="sc-doc-note">Past due: submit it so your scholarship becomes active again.</span>@endif</dd></div>@endif
                        <div><dt>Reviewed</dt><dd>{{ $r->reviewed_at && $r->status !== 'submitted' ? $r->reviewed_at->format('M j, Y') : 'Not yet' }}</dd></div>
                    </dl>

                    @if ($r->instructions)
                        <div class="sc-remark is-info"><i class="bi bi-info-circle" aria-hidden="true"></i><span><strong>What to submit:</strong> {{ $r->instructions }}</span></div>
                    @endif
                    @if ($r->review_note)
                        <div class="sc-remark"><i class="bi bi-chat-left-text" aria-hidden="true"></i><span><strong>Note from {{ $provider }}:</strong> {{ $r->review_note }}</span></div>
                    @endif

                    <div class="sc-doc-foot">
                        <div class="sc-doc-file">
                            <span class="sc-doc-icon" aria-hidden="true"><i class="bi bi-paperclip"></i></span>
                            <div>
                                @if ($r->hasFile())
                                    <strong>
                                        @if ($previewable)
                                            <button type="button" class="sc-link-btn" data-preview-url="{{ $fileUrl }}?inline=1" data-preview-download="{{ $fileUrl }}" data-preview-name="{{ $r->original_name }}" data-preview-type="{{ $r->mime === 'application/pdf' ? 'pdf' : 'image' }}" data-preview-meta="{{ $r->name }} · sent {{ $r->submitted_at?->format('M j, Y') }}">{{ $r->original_name }}</button>
                                        @else
                                            {{ $r->original_name }}
                                        @endif
                                    </strong>
                                    <span class="dash-meta">
                                        <span>{{ strtoupper(pathinfo((string) $r->original_name, PATHINFO_EXTENSION)) }} file{{ $r->size ? ', '.($r->size >= 1048576 ? number_format($r->size / 1048576, 1).' MB' : max(1, round($r->size / 1024)).' KB') : '' }}</span>
                                        <a href="{{ $fileUrl }}"><i class="bi bi-download" aria-hidden="true"></i>Download</a>
                                    </span>
                                @else
                                    <strong>No file uploaded yet</strong>
                                    <small>Accepted: {{ $r->uploadRulesLabel() }}.</small>
                                @endif
                            </div>
                        </div>
                        @if ($rowCanUpload)
                            <button type="button" class="dash-btn" {!! $uploadData !!}><i class="bi bi-upload" aria-hidden="true"></i>{{ $action }} document</button>
                        @endif
                    </div>

                    @if ($rowCanUpload)
                        <noscript>
                            <form method="POST" action="{{ route('scholar.requirements.upload', $r->id) }}" enctype="multipart/form-data" class="dash-inline-form sch-upload">
                                @csrf
                                <label class="visually-hidden" for="file-{{ $r->id }}">File for {{ $r->name }}</label>
                                <input id="file-{{ $r->id }}" type="file" name="file" accept="{{ \App\Support\MaintainingDocument::accept($r->acceptedTypes()) }}" required>
                                <button type="submit" class="dash-btn is-sm"><i class="bi bi-upload" aria-hidden="true"></i>{{ $action }}</button>
                            </form>
                        </noscript>
                    @endif
                </div>
            </div>
        </li>
    @endforeach
</ul>
