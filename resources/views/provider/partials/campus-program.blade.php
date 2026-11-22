{{-- Campus, then the programs that campus offers: two plain selects, the same pattern as the student's
     matching form (campus → program). public/js/bakr-provider.js shows only the chosen campus'
     programs; without JS every program is listed and the server still refuses a program the campus does not offer.
     $campusPrograms = ProgramCatalog::byCampus()  [campus name => [program names]]
     $campus, $program  the current values (old input or the saved scholar)
     $required          true when adding a scholar (both are needed); false on the profile (older scholars may have none) --}}
@php
    $required = $required ?? true;
    $campus = (string) ($campus ?? '');
    $program = (string) ($program ?? '');
@endphp
<div class="dash-field is-wide">
    <label for="campus">Campus</label>
    <select id="campus" name="campus" data-campus-select @required($required)>
        <option value="">Choose the campus</option>
        @foreach (array_keys($campusPrograms) as $name)
            <option value="{{ $name }}" @selected($campus === $name)>{{ $name }}</option>
        @endforeach
    </select>
    @error('campus')<p class="dash-error">{{ $message }}</p>@enderror
</div>
<div class="dash-field is-wide">
    <label for="program">Program</label>
    <select id="program" name="program" data-program-select @required($required) aria-describedby="program-help">
        <option value="">Choose the program</option>
        @foreach ($campusPrograms as $name => $programs)
            @foreach ($programs as $p)
                <option value="{{ $p }}" data-campus="{{ $name }}" @selected($campus === $name && $program === $p)>{{ $p }}</option>
            @endforeach
        @endforeach
    </select>
    <small id="program-help">Only the programs DNSC offers at the chosen campus are listed.</small>
    @error('program')<p class="dash-error">{{ $message }}</p>@enderror
</div>
