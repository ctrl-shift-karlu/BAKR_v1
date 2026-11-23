{{-- A scholar's personal / contact details (owner, 2026-11-20: all required; gender Male or Female),
     shared by Add scholars and the scholar's profile so both forms look and validate the same
     (ScholarRoster::personalRules()).
     $fields  which of: birthdate, gender, mobile, address (in this order on the page)
     $values  current values [field => value] (old input, or the saved scholar)
     birthdate + gender, mobile, city and province are half-width; the street is full width. --}}
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
            {{-- Address in parts (owner, 2026-11-23): street, city / municipality, province. --}}
            <div class="dash-field is-wide">
                <label for="address_street">Street</label>
                <input id="address_street" name="address_street" maxlength="150" required autocomplete="off" value="{{ $values['address_street'] ?? '' }}" placeholder="e.g. Purok 3, Barangay Gredu" aria-describedby="address_street-help">
                <small id="address_street-help">House no., street, purok or barangay.</small>
                @error('address_street')<p class="dash-error">{{ $message }}</p>@enderror
            </div>
            <div class="dash-field">
                <label for="address_city">City / Municipality</label>
                <input id="address_city" name="address_city" maxlength="80" required autocomplete="off" value="{{ $values['address_city'] ?? '' }}" placeholder="e.g. Panabo City">
                @error('address_city')<p class="dash-error">{{ $message }}</p>@enderror
            </div>
            <div class="dash-field">
                <label for="address_province">Province</label>
                <input id="address_province" name="address_province" maxlength="80" required autocomplete="off" value="{{ $values['address_province'] ?? '' }}" placeholder="e.g. Davao del Norte">
                @error('address_province')<p class="dash-error">{{ $message }}</p>@enderror
            </div>
            @break
    @endswitch
@endforeach
