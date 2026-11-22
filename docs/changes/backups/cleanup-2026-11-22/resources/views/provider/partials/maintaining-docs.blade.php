{{-- My scholarships › Maintaining documents (owner, 2026-11-19): what scholars submit every semester,
     each with its own name, instructions, accepted files, size limit and who must submit it.
     Only for providers that collect requirements in BAKR. Read view by default; "Edit documents"
     (?documents=1) shows this section's OWN form (separate from the other sections' forms), saved by
     provider.scholarships.documents. "Add a document" copies the <template> (bakr-provider.js);
     without JavaScript one blank row is always there for adding. --}}
@php
    $MD = \App\Support\MaintainingDocument::class;
    $docs = $s->maintainingDocuments();
    $readUrl = route('provider.scholarships', ['s' => $s->id]).'#maintaining';
    $editUrl = route('provider.scholarships', ['s' => $s->id, 'documents' => 1]).'#maintaining';
@endphp
<details class="dash-card ms-section ms-fold md-section {{ $editing ? 'is-editing' : '' }}" id="maintaining" @if ($editing) open @endif>
    <summary>
        <h3 class="dash-card-title" id="md-title"><i class="bi bi-folder-check" aria-hidden="true"></i>Maintaining documents <span class="ms-private">Only your scholars see this</span></h3>
        @unless ($editing)
            <a class="dash-btn is-soft is-sm ms-edit" href="{{ $editUrl }}"><i class="bi bi-pencil-square" aria-hidden="true"></i>{{ $docs ? 'Edit' : 'Add' }}</a>
        @endunless
    </summary>
    <div class="ms-fold-body">
    <p class="ms-hint">What your scholars upload every semester to keep the scholarship. Each document has its own accepted files and size limit, and each semester's checklist is built from this list when you set that semester's deadline on the Requirements page.</p>

    @if (! $editing)
        {{-- ============ READ VIEW ============ --}}
        @forelse ($docs as $d)
            @if ($loop->first)<ol class="md-list">@endif
            <li>
                <span class="md-num" aria-hidden="true">{{ $loop->iteration }}</span>
                <div class="md-body">
                    <strong>{{ $d['name'] }}</strong>
                    @if ($d['note'])<p>{{ $d['note'] }}</p>@endif
                    <div class="md-tags">
                        <span class="md-tag"><i class="bi bi-file-earmark" aria-hidden="true"></i>{{ $MD::typesLabel($d['types']) }}</span>
                        <span class="md-tag"><i class="bi bi-hdd" aria-hidden="true"></i>Up to {{ $d['max_mb'] }} MB</span>
                        <span class="md-tag {{ $d['required'] ? 'is-required' : '' }}"><i class="bi {{ $d['required'] ? 'bi-people' : 'bi-person-check' }}" aria-hidden="true"></i>{{ $d['required'] ? 'Every scholar' : 'Only if it applies' }}</span>
                    </div>
                </div>
            </li>
            @if ($loop->last)</ol>@endif
        @empty
            <p class="pv-missing">None set yet. Scholars cannot upload maintaining documents until you add them.</p>
        @endforelse
    @else
        {{-- ============ ITS OWN FORM ============ --}}
        @php
            // After a failed save: the typed rows, KEEPING their indices so each error shows on its own row.
            $rows = array_filter((array) (old('docs') ?? $docs), fn ($r) => is_array($r) && trim((string) ($r['name'] ?? '')) !== '');
            $blank = $rows ? max(array_map('intval', array_keys($rows))) + 1 : 0;
        @endphp
        <form method="POST" action="{{ route('provider.scholarships.documents', $s->id) }}" class="md-form" id="md-form-{{ $s->id }}" data-doc-form data-next-index="{{ $blank + 1 }}">
            @csrf
            @method('PUT')
            @error('docs')<div class="dash-flash is-error" role="alert"><i class="bi bi-exclamation-triangle" aria-hidden="true"></i><span>{{ $message }}</span></div>@enderror

            <div class="md-rows" data-doc-rows>
                @foreach ($rows as $i => $row)
                    @include('provider.partials.maintaining-doc-row', ['i' => $i, 'doc' => $row, 'n' => $loop->iteration])
                @endforeach
                {{-- Always one blank row, so a document can be added without JavaScript. --}}
                @include('provider.partials.maintaining-doc-row', ['i' => $blank, 'doc' => null, 'n' => count($rows) + 1])
            </div>

            <template data-doc-template>
                @include('provider.partials.maintaining-doc-row', ['i' => '__I__', 'doc' => null, 'n' => '__N__'])
            </template>

            <div class="md-actions">
                <button type="button" class="dash-btn is-soft" data-doc-add hidden><i class="bi bi-plus-lg" aria-hidden="true"></i>Add a document</button>
                <span class="md-actions-end">
                    <a class="dash-btn is-soft" href="{{ $readUrl }}">Cancel</a>
                    <button type="submit" class="dash-btn"><i class="bi bi-check2" aria-hidden="true"></i>Save documents</button>
                </span>
            </div>
            <small class="md-foot">Saving changes the documents' settings on every checklist item that is not approved yet. Every save is recorded with your account and the time.</small>
        </form>
    @endif
    </div>
</details>
