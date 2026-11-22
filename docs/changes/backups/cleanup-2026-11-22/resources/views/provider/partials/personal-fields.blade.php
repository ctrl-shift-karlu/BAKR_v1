{{-- A scholar's personal / contact details (owner, 2026-11-20: all required; gender Male or Female),
     shared by Add scholars and the scholar's profile so both forms look and validate the same
     (ScholarRoster::personalRules()).
     $fields  which of: birthdate, gender, mobile, address (in this order on the page)
     $values  current values [field => value] (old input, or the saved scholar)
     birthdate + gender and mobile are half-width; address is full width. --}}
@php
    $values = $values ?? [];
    $oldest = '1900-01-02';
    $youngest = now()->subYears(10)->toDateString();
@endphp
@foreach ($fields as $field)
    @switch ($field)
        @case('birthdate')
            <div class="dash-field">
                <label for="birthdate">Birthday</label>
                <input id="birthdate" name="birthdate" type="date" required min="{{ $oldest }}" max="{{ $youngest }}" value="{{ $values['birthdate'] ?? '' }}">
                @error('birthdate')<p class="dash-error">{{ $message }}</p>@enderror
            </div>
            @break
        @case('gender')
            <div class="dash-field">
                <label for="gender">Gender</label>
                <select id="gender" name="gender" required>
                    <option value="">Choose…</option>
                    @foreach (\App\Models\Scholar::GENDERS as $key => $label)
                        <option value="{{ $key }}" @selected(($values['gender'] ?? '') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('gender')<p class="dash-error">{{ $message }}</p>@enderror
            </div>
            @break
        @case('mobile')
            <div class="dash-field">
                <label for="mobile">Mobile number</label>
                <input id="mobile" name="mobile" type="tel" inputmode="tel" maxlength="20" required autocomplete="off" value="{{ $values['mobile'] ?? '' }}" placeholder="e.g. 0917 123 4567">
                @error('mobile')<p class="dash-error">{{ $message }}</p>@enderror
            </div>
            @break
        @case('address')
            <div class="dash-field is-wide">
                <label for="address">Address</label>
                <input id="address" name="address" maxlength="255" required autocomplete="off" value="{{ $values['address'] ?? '' }}" placeholder="e.g. Purok 3, Barangay Gredu, Panabo City, Davao del Norte">
                @error('address')<p class="dash-error">{{ $message }}</p>@enderror
            </div>
            @break
    @endswitch
@endforeach
