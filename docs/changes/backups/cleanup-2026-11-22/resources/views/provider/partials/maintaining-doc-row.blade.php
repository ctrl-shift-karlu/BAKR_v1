{{-- One maintaining document in the My scholarships › Maintaining documents form.
     $i: row index (or "__I__" in the <template> that "Add a document" copies), $doc: {name, note, types,
     max_mb, required} (or null for a new, blank row), $n: number shown in the header.
     A blank new row is simply skipped on save; "Remove" leaves a row out. --}}
@php
    $MD = \App\Support\MaintainingDocument::class;
    $isNew = $doc === null;
    $doc ??= ['name' => '', 'note' => null, 'types' => $MD::DEFAULT_TYPES, 'max_mb' => $MD::DEFAULT_MB, 'required' => true];
    $types = (array) ($doc['types'] ?? []);
    $required = filter_var($doc['required'] ?? true, FILTER_VALIDATE_BOOLEAN);
    $removed = ! empty($doc['remove']);
    $id = 'doc-'.$i;
@endphp
<fieldset class="md-row {{ $isNew ? 'is-new' : '' }} {{ $removed ? 'is-removed' : '' }}" data-doc-row>
    <legend class="md-row-head">
        <span class="md-num" aria-hidden="true">{{ $n }}</span>
        <span class="md-row-title">{{ $isNew ? 'New document' : 'Document '.$n }}</span>
    </legend>
    @unless ($isNew)
        <label class="md-remove">
            <input type="checkbox" name="docs[{{ $i }}][remove]" value="1" data-doc-remove @checked($removed)>
            <i class="bi bi-trash3" aria-hidden="true"></i><span>Remove</span>
        </label>
    @endunless

    <div class="dash-field">
        <label for="{{ $id }}-name">Document name</label>
        {{-- Two lines: official document names are long. Line breaks are joined on save. --}}
        <textarea id="{{ $id }}-name" name="docs[{{ $i }}][name]" rows="2" maxlength="{{ $MD::NAME_MAX }}" class="md-name"
               placeholder="e.g. Certificate of Registration (COR) for this semester" @unless ($isNew) required @endunless>{{ $doc['name'] ?? '' }}</textarea>
        @error("docs.$i.name")<p class="dash-error">{{ $message }}</p>@enderror
        @if ($isNew)<small>Leave empty if you have nothing to add.</small>@endif
    </div>

    <div class="dash-field">
        <label for="{{ $id }}-note">Instructions for scholars <span class="md-optional">optional</span></label>
        <textarea id="{{ $id }}-note" name="docs[{{ $i }}][note]" rows="2" maxlength="{{ $MD::NOTE_MAX }}" placeholder="e.g. Scan the signed copy with the registrar's seal visible.">{{ $doc['note'] ?? '' }}</textarea>
        @error("docs.$i.note")<p class="dash-error">{{ $message }}</p>@enderror
    </div>

    <div class="md-grid">
        <fieldset class="md-types">
            <legend>Accepted files</legend>
            <div class="md-chips">
                @foreach ($MD::TYPES as $key => [$label])
                    <label class="md-chip">
                        <input type="checkbox" name="docs[{{ $i }}][types][]" value="{{ $key }}" @checked(in_array($key, $types, true))>
                        <span>{{ $label }}</span>
                    </label>
                @endforeach
            </div>
            @error("docs.$i.types")<p class="dash-error">{{ $message }}</p>@enderror
            <small>Only PDF, JPG and PNG can be previewed in BAKR; Word files are downloaded to view.</small>
        </fieldset>

        <div class="dash-field">
            <label for="{{ $id }}-size">Largest file</label>
            <select id="{{ $id }}-size" name="docs[{{ $i }}][max_mb]">
                @foreach ($MD::SIZES as $mb)
                    <option value="{{ $mb }}" @selected((int) ($doc['max_mb'] ?? $MD::DEFAULT_MB) === $mb)>{{ $mb }} MB</option>
                @endforeach
            </select>
        </div>
    </div>

    <fieldset class="md-who">
        <legend>Who submits it</legend>
        <label class="md-radio">
            <input type="radio" name="docs[{{ $i }}][required]" value="1" @checked($required)>
            <span><strong>Every scholar, every semester</strong><small>Missing after the due date makes the scholar Inactive and holds back their stipend.</small></span>
        </label>
        <label class="md-radio">
            <input type="radio" name="docs[{{ $i }}][required]" value="0" @checked(! $required)>
            <span><strong>Only scholars it applies to</strong><small>e.g. “If returning from a leave of absence”. Never makes anyone Inactive.</small></span>
        </label>
    </fieldset>
</fieldset>
