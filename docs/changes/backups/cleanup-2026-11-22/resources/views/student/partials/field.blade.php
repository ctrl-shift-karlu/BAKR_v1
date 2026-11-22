@php
    $value = old($key, $saved[$key] ?? null);
    // Questions that only apply to some students: 'when' = first_year | incoming | upper (ApplicationSchema::applies);
    // the form script shows / hides them as the student picks a status and year level. An incoming student has no year-level question.
    $when = $f['when'] ?? '';
    $hideWhen = $key === 'year_level' ? 'incoming_first_year' : '';
    // Every question is answered (2026-11-21): "awards in that skill" is required whenever it is shown (after a real skill).
    $required = ! empty($f['required']) || ! empty($f['required_if']) || $when === 'talent';
    $hasError = $errors->has($key) || $errors->has($key . '.0');
    $selected = array_map('strval', (array) $value);
    $info = $f['info'] ?? null;   // short explanation of an unfamiliar question (ApplicationSchema 'info')
@endphp
<div class="sp-field {{ $hasError ? 'has-error' : '' }} {{ $info ? 'has-info' : '' }}" data-field="{{ $key }}" data-type="{{ $f['type'] }}" data-required="{{ $required ? '1' : '0' }}" data-label="{{ $f['label'] }}" data-when="{{ $when }}" data-hide-when="{{ $hideWhen }}" @if (! empty($f['exclusive'])) data-exclusive="{{ $f['exclusive'] }}" @endif>
    {{-- The "i" sits next to the question (outside the <label>, so tapping it never ticks an answer). Desktop: hover or
         keyboard focus shows the note; every device: a tap / click toggles it (student-form.js). Text only. --}}
    <div class="sp-q">
    @if (in_array($f['type'], ['radio', 'checkbox']))
        <span class="sp-label" id="lbl-{{ $key }}">@isset($num)<span class="bf-num">{{ $num }}</span>@endisset{{ $f['label'] }}@if ($required)<span class="sp-req" aria-hidden="true">*</span>@endif</span>
    @else
        <label for="f-{{ $key }}">@isset($num)<span class="bf-num">{{ $num }}</span>@endisset{{ $f['label'] }}@if ($required)<span class="sp-req" aria-hidden="true">*</span>@endif</label>
    @endif
    @if ($info)
        <button type="button" class="sp-info" aria-label="What this question means" aria-expanded="false" aria-controls="info-{{ $key }}" data-info-toggle><i class="bi bi-info-circle" aria-hidden="true"></i></button>
    @endif
    </div>
    @if ($info)
        <p class="sp-tip" id="info-{{ $key }}" role="note">{{ $info }}</p>
    @endif

    @if ($f['type'] === 'radio')
        <div class="sp-choices" role="radiogroup" aria-labelledby="lbl-{{ $key }}">
            @foreach ($f['options'] as $val => $label)
                <label class="sp-choice"><input type="radio" name="{{ $key }}" value="{{ $val }}" {{ in_array((string) $val, $selected, true) ? 'checked' : '' }}><span>{{ $label }}</span></label>
            @endforeach
        </div>
    @elseif ($f['type'] === 'checkbox')
        <div class="sp-choices stack" role="group" aria-labelledby="lbl-{{ $key }}">
            @foreach ($f['options'] as $val => $label)
                <label class="sp-choice box"><input type="checkbox" name="{{ $key }}[]" value="{{ $val }}" {{ in_array((string) $val, $selected, true) ? 'checked' : '' }}><span>{{ $label }}</span></label>
            @endforeach
        </div>
    @elseif ($f['type'] === 'campus')
        <select class="sp-input" id="f-{{ $key }}" name="{{ $key }}" data-campus-select>
            <option value="">Choose your campus</option>
            @foreach ($campuses as $c)
                <option value="{{ $c->id }}" {{ in_array((string) $c->id, $selected, true) ? 'selected' : '' }}>{{ $c->campus_name }}</option>
            @endforeach
        </select>
    @elseif ($f['type'] === 'program')
        <select class="sp-input" id="f-{{ $key }}" name="{{ $key }}" data-program-select>
            <option value="">Choose your program</option>
            @foreach ($programs as $p)
                <option value="{{ $p->id }}" data-campus="{{ $p->campus_id }}" {{ in_array((string) $p->id, $selected, true) ? 'selected' : '' }}>{{ $p->program_name }}</option>
            @endforeach
        </select>
    @elseif ($f['type'] === 'select')
        <select class="sp-input" id="f-{{ $key }}" name="{{ $key }}">
            <option value="">Choose one</option>
            @foreach ($f['options'] as $val => $label)
                <option value="{{ $val }}" {{ in_array((string) $val, $selected, true) ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    @elseif ($f['type'] === 'number')
        <input class="sp-input" type="number" id="f-{{ $key }}" name="{{ $key }}" value="{{ $value }}" min="{{ $f['min'] }}" max="{{ $f['max'] }}" step="{{ $f['step'] ?? '1' }}" inputmode="{{ ($f['step'] ?? '1') === '1' ? 'numeric' : 'decimal' }}" placeholder="{{ $f['placeholder'] ?? '' }}">
    @elseif ($f['type'] === 'date')
        <input class="sp-input" type="date" id="f-{{ $key }}" name="{{ $key }}" value="{{ $value }}" min="1950-01-02" max="{{ now()->subYears(14)->toDateString() }}" autocomplete="bday">
    @else
        <input class="sp-input" type="text" id="f-{{ $key }}" name="{{ $key }}" value="{{ $value }}" maxlength="{{ $f['max'] ?? 120 }}" placeholder="{{ $f['placeholder'] ?? '' }}">
    @endif

    @if (! empty($f['help']))
        <p class="sp-help">{{ $f['help'] }}</p>
    @endif
    @if ($hasError)
        <p class="sp-err" role="alert">{{ $errors->first($key) ?: $errors->first($key . '.0') }}</p>
    @endif
</div>
