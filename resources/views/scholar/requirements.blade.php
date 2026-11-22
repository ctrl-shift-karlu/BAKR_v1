@extends('scholar.layout')
@section('title', 'Requirements | BAKR')

{{-- Scholar portal › Requirements. Modelled on the scholar prototype's "Documents" view: per scholarship
     a summary (count, alert, status tiles) and an accordion row per maintaining document, with an upload
     dialog (drag and drop, file check, progress) and a preview. Each scholarship lists its OWN documents,
     and a file goes only to that scholarship's provider (ScholarRequirementController::upload → their review
     queue). Behaviour: public/js/bakr-scholar-requirements.js. Without JavaScript each row has a plain
     upload form, so submitting always works. Providers that don't collect documents in BAKR get a
     "submit directly" card. --}}

@php
    $state = [
        'missing' => ['Not submitted', '', 'bi-upload', 'Not submitted yet.'],
        'submitted' => ['Waiting for review', 'is-info', 'bi-hourglass-split', 'Your provider has your file and will check it.'],
        'needs_revision' => ['Needs revision', 'is-warn', 'bi-arrow-repeat', 'Upload a corrected copy.'],
        'approved' => ['Approved', 'is-ok', 'bi-check2-circle', 'Checked and accepted by your provider.'],
    ];
    $inlineTypes = ['application/pdf', 'image/jpeg', 'image/png'];
@endphp

@section('hero_title')Maintaining <em>requirements</em>@endsection
@section('hero_sub', 'Submit the documents each scholarship asks for every semester. Your provider reviews them here.')

@section('portal')
@if ($errors->any())
    <div class="dash-flash is-error" role="alert"><i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i><ul>@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
@endif

@foreach ($scholars as $sc)
    @php
        $s = $sc->scholarship;
        $byTerm = $sc->requirements->toBase()->groupBy('term');   // plain collection: except() works on term keys
        $now = $byTerm->get($term, collect());
        $older = $byTerm->except($term);
        // Counts use only what the scholar owes ("only if it applies" items count once sent); every item is still listed.
        $owed = $now->filter->isOwed();
        $count = fn ($st) => $owed->where('status', $st)->count();
        $total = $owed->count();
        $sent = $count('submitted') + $count('approved');
        $todo = $count('missing') + $count('needs_revision');
    @endphp

    <section class="dash-card sc-docs {{ $loop->first ? '' : 'pv-gap' }}" id="sch-{{ $sc->id }}" aria-labelledby="docs-title-{{ $sc->id }}">
        @if (! $s->offers('requirements'))
            {{-- ============ PROVIDER COLLECTS DIRECTLY ============ --}}
            <div class="sc-docs-summary">
                <span class="dash-avatar is-{{ ($loop->index % 4) + 1 }} sc-docs-badge" aria-hidden="true">{{ $s->shortName() }}</span>
                <div class="sc-docs-headline">
                    <h2 class="sc-docs-title" id="docs-title-{{ $sc->id }}">{{ $s->name }}</h2>
                    <p class="sc-docs-sub">{{ $s->provider }} · {{ $term }}</p>
                    <p class="sc-docs-alert is-info"><span class="sc-dot" aria-hidden="true"><i class="bi bi-building"></i></span>Submit your maintaining documents in person at the official DNSC scholarship office ({{ config('bakr.scholarship_office') }}). They are not uploaded in BAKR.</p>
                    <p class="sc-docs-note">Bring the originals or copies they ask for, each semester. If they are not complete, {{ $s->provider }} can mark your scholarship Inactive.</p>
                </div>
            </div>
            @if (! $s->maintainingDocuments())
                <p class="dash-empty">Ask the {{ config('bakr.scholarship_office') }} what to submit each semester.</p>
            @else
                <ul class="sc-list is-check sc-docs-plain">@foreach ($s->maintainingDocuments() as $d)<li>{{ $d['name'] }}@unless ($d['required']) <small>(only if it applies to you)</small>@endunless</li>@endforeach</ul>
            @endif
        @else
            {{-- ============ SUMMARY ============ --}}
            <div class="sc-docs-summary">
                <div class="sc-docs-count" aria-label="{{ $sent }} of {{ $total }} documents submitted">
                    <strong>{{ $sent }}/{{ $total }}</strong><span>submitted</span>
                </div>
                <div class="sc-docs-headline">
                    <h2 class="sc-docs-title" id="docs-title-{{ $sc->id }}">{{ $s->name }}</h2>
                    <p class="sc-docs-sub">Goes to {{ $s->provider }} · {{ $term }}</p>
                    @if ($total === 0 && ! $sc->startedBy($term))
                        <p class="sc-docs-alert is-info"><span class="sc-dot" aria-hidden="true"><i class="bi bi-calendar-check"></i></span>Your scholarship starts in {{ $sc->started_term }}. Your maintaining documents start from that semester.</p>
                    @elseif ($total === 0)
                        <p class="sc-docs-alert is-info"><span class="sc-dot" aria-hidden="true"><i class="bi bi-calendar-check"></i></span>{{ $s->provider }} has not opened the {{ $term }} checklist yet. You will get an e-mail with the deadline when they do.</p>
                    @elseif ($todo === 0)
                        <p class="sc-docs-alert is-clear"><span class="sc-dot" aria-hidden="true"><i class="bi bi-check-lg"></i></span>Nothing needs your action right now.</p>
                    @else
                        <p class="sc-docs-alert"><span class="sc-dot" aria-hidden="true">!</span>{{ $todo }} {{ \Illuminate\Support\Str::plural('document', $todo) }} {{ $todo === 1 ? 'needs' : 'need' }} your action:
                            {{ collect([$count('needs_revision') ? $count('needs_revision').' to revise' : null, $count('missing') ? $count('missing').' to upload' : null])->filter()->implode(', ') }}</p>
                    @endif
                    <p class="sc-docs-note">
                        @if ($total === 0)
                            Upload buttons appear here once they do. You can prepare the documents below now.
                        @elseif ($todo === 0)
                            Every document has been sent. If {{ $s->provider }} asks for a correction, it will show here with their note.
                        @else
                            Open a document to see its status, what files it takes, and send your file.
                        @endif
                    </p>
                </div>
                @if ($total)
                    <ul class="sc-tiles" aria-label="Document summary">
                        {{-- A tile is tinted only when it has something in it (a gold "0" read as a warning). --}}
                        <li class="{{ $count('approved') ? 'is-ok' : '' }}"><strong>{{ $count('approved') }}</strong><span>Approved</span></li>
                        <li class="{{ $count('submitted') ? 'is-info' : '' }}"><strong>{{ $count('submitted') }}</strong><span>Waiting for review</span></li>
                        <li class="{{ $count('needs_revision') ? 'is-warn' : '' }}"><strong>{{ $count('needs_revision') }}</strong><span>Needs revision</span></li>
                        <li class="{{ $count('missing') ? 'is-todo' : '' }}"><strong>{{ $count('missing') }}</strong><span>Not submitted</span></li>
                        <li><strong>{{ $total }}</strong><span>Total</span></li>
                    </ul>
                @endif
            </div>

            {{-- ============ DOCUMENTS (this term) ============ --}}
            @if ($total === 0)
                @if (! $s->maintainingDocuments())
                    <p class="dash-empty">{{ $s->provider }} has not listed its maintaining documents yet.</p>
                @else
                    <ul class="sc-doc-list">
                        @foreach ($s->maintainingDocuments() as $d)
                            <li class="sc-doc is-closed">
                                <div class="sc-doc-head">
                                    <span class="sc-doc-icon" aria-hidden="true"><i class="bi bi-file-earmark-text"></i></span>
                                    <span class="sc-doc-title">
                                        <strong>{{ $d['name'] }}</strong>
                                        <span class="dash-meta">
                                            <span>{{ $d['note'] ?: 'Prepare it now; upload when the checklist opens.' }}</span>
                                            <span><i class="bi bi-file-earmark" aria-hidden="true"></i>{{ \App\Support\MaintainingDocument::typesLabel($d['types']) }}, up to {{ $d['max_mb'] }} MB</span>
                                            @unless ($d['required'])<span><i class="bi bi-question-circle" aria-hidden="true"></i>Only if it applies to you</span>@endunless
                                        </span>
                                    </span>
                                    <span class="dash-status">Not open yet</span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            @else
                @include('scholar.partials.requirement-rows', ['items' => $now, 'state' => $state, 'inlineTypes' => $inlineTypes, 'provider' => $s->provider, 'canUpload' => ! $sc->isTerminated()])
            @endif

            @if ($older->isNotEmpty())
                <details class="sc-older">
                    <summary>Earlier semesters ({{ $older->count() }})</summary>
                    @foreach ($older as $label => $items)
                        <h3 class="pv-term">{{ $label }} <small>{{ $items->where('status', 'approved')->count() }}/{{ $items->count() }} approved</small></h3>
                        @include('scholar.partials.requirement-rows', ['items' => $items, 'state' => $state, 'inlineTypes' => $inlineTypes, 'provider' => $s->provider, 'canUpload' => ! $sc->isTerminated()])
                    @endforeach
                </details>
            @endif

            <p class="sch-help"><i class="bi bi-shield-lock" aria-hidden="true"></i> Files are private: only you and {{ $s->provider }} can open them.</p>
        @endif
    </section>
@endforeach

{{-- Dialogs only when at least one scholarship collects documents in BAKR (none for DNSC-only scholars). --}}
@if ($scholars->contains(fn ($sc) => $sc->scholarship->offers('requirements')))
{{-- ============ UPLOAD DIALOG ============ --}}
<dialog class="dash-dialog sc-upload" id="sc-upload" aria-labelledby="sc-upload-title">
    {{-- Accepted types and size are set per document (data-upload-* on each Upload button); these are the defaults. --}}
    <form method="POST" action="" enctype="multipart/form-data" id="sc-upload-form" novalidate
          data-types="{{ implode(',', \App\Support\MaintainingDocument::extensions(\App\Support\MaintainingDocument::DEFAULT_TYPES)) }}" data-max-kb="{{ \App\Support\MaintainingDocument::DEFAULT_MB * 1024 }}">
        @csrf
        <div class="sc-modal-head">
            <h2 id="sc-upload-title">Upload document</h2>
            <div class="sc-modal-doc">
                <span class="sc-doc-icon" aria-hidden="true"><i class="bi bi-file-earmark-arrow-up"></i></span>
                <div><strong id="sc-upload-name"></strong><small id="sc-upload-meta"></small></div>
            </div>
            <button type="button" class="dash-dialog-close" data-close-dialog aria-label="Close"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
        </div>

        <div class="sc-modal-body">
            <p class="sc-modal-current" id="sc-upload-current" hidden></p>

            <div class="sc-dz" id="sc-dz">
                <span class="sc-dz-icon" aria-hidden="true"><i class="bi bi-cloud-arrow-up"></i></span>
                <strong>Drag and drop your file here</strong>
                <span>or browse from your device</span>
                <button type="button" class="dash-btn is-sm" id="sc-browse"><i class="bi bi-folder2-open" aria-hidden="true"></i>Browse files</button>
                <small id="sc-dz-rules">PDF, JPG or PNG. Up to 5 MB.</small>
            </div>

            <div class="sc-file" id="sc-file" hidden>
                <span class="sc-doc-icon" aria-hidden="true"><i class="bi bi-file-earmark-check"></i></span>
                <div><strong id="sc-file-name"></strong><small id="sc-file-meta"></small></div>
                <div class="sc-file-actions">
                    <button type="button" class="dash-btn is-soft is-sm" id="sc-change">Change</button>
                    <button type="button" class="dash-btn is-danger-soft is-sm" id="sc-remove"><i class="bi bi-trash3" aria-hidden="true"></i>Remove</button>
                </div>
            </div>

            <div class="sc-modal-error" id="sc-upload-error" role="alert" hidden><i class="bi bi-exclamation-circle" aria-hidden="true"></i><span></span></div>
            <div class="sc-progress" id="sc-progress" hidden><span></span></div>

            <label class="visually-hidden" for="sc-input">File</label>
            <input type="file" name="file" id="sc-input" accept="{{ \App\Support\MaintainingDocument::accept(\App\Support\MaintainingDocument::DEFAULT_TYPES) }}" hidden>
        </div>

        <div class="dash-dialog-foot">
            <button type="button" class="dash-btn is-soft" data-close-dialog id="sc-cancel">Cancel</button>
            <button type="submit" class="dash-btn" id="sc-submit"><i class="bi bi-upload" aria-hidden="true"></i><span>Upload</span></button>
        </div>
    </form>
</dialog>

{{-- ============ PREVIEW DIALOG ============ --}}
<dialog class="dash-dialog sc-preview" id="sc-preview" aria-labelledby="sc-preview-name">
    <div class="sc-modal-head">
        <h2>Preview</h2>
        <div class="sc-modal-doc"><div><strong id="sc-preview-name"></strong><small id="sc-preview-meta"></small></div></div>
        <button type="button" class="dash-dialog-close" data-close-dialog aria-label="Close preview"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
    </div>
    <div class="sc-preview-stage" id="sc-preview-stage"></div>
    <div class="dash-dialog-foot">
        <a class="dash-btn is-soft" id="sc-preview-download" href="#"><i class="bi bi-download" aria-hidden="true"></i>Download</a>
        <button type="button" class="dash-btn" data-close-dialog>Close</button>
    </div>
</dialog>
@push('scripts')
    <script src="{{ asset('js/bakr-scholar-requirements.js') }}"></script>
@endpush
@endif
@endsection
