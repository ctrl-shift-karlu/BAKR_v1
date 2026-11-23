{{-- A scholar's name in parts (owner, 2026-11-23): first name + surname required, middle name + suffix optional.
     Shared by Add scholars and the Personal card of the scholar's profile (App\Support\PersonName rules).
     $values  [first_name, middle_name, last_name, suffix] (old input, or the saved name) --}}
@php $values = $values ?? []; @endphp
<div class="dash-field">
    <label for="first_name">First name</label>
    <input id="first_name" name="first_name" maxlength="{{ \App\Support\PersonName::PART_MAX }}" required autocomplete="off" value="{{ $values['first_name'] ?? '' }}" placeholder="e.g. Juan">
    @error('first_name')<p class="dash-error">{{ $message }}</p>@enderror
</div>
<div class="dash-field">
    <label for="middle_name">Middle name <small class="pv-optional">(optional)</small></label>
    <input id="middle_name" name="middle_name" maxlength="{{ \App\Support\PersonName::PART_MAX }}" autocomplete="off" value="{{ $values['middle_name'] ?? '' }}" placeholder="e.g. Andres">
    @error('middle_name')<p class="dash-error">{{ $message }}</p>@enderror
</div>
<div class="dash-field">
    <label for="last_name">Surname</label>
    <input id="last_name" name="last_name" maxlength="{{ \App\Support\PersonName::PART_MAX }}" required autocomplete="off" value="{{ $values['last_name'] ?? '' }}" placeholder="e.g. Dela Cruz">
    @error('last_name')<p class="dash-error">{{ $message }}</p>@enderror
</div>
<div class="dash-field">
    <label for="suffix">Suffix <small class="pv-optional">(optional)</small></label>
    <select id="suffix" name="suffix">
        <option value="">None</option>
        @foreach (\App\Support\PersonName::SUFFIXES as $key => $label)
            <option value="{{ $key }}" @selected(($values['suffix'] ?? '') === $key)>{{ $label }}</option>
        @endforeach
    </select>
    @error('suffix')<p class="dash-error">{{ $message }}</p>@enderror
</div>
