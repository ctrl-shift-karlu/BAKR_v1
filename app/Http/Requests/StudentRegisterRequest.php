<?php

namespace App\Http\Requests;

use App\Support\PersonName;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

/**
 * Validation of the student registration form: the name in parts (first name + surname required, middle name + suffix
 * optional; owner, 2026-11-23), a unique e-mail and a password of at least 8 characters with letters and numbers.
 */
class StudentRegisterRequest extends FormRequest
{
    /** Anyone may register a student account. */
    public function authorize(): bool
    {
        return true; // public registration for the matching platform
    }

    /** "jr" → "Jr." before validation, so a typed suffix still matches the list. */
    protected function prepareForValidation(): void
    {
        $this->merge(['suffix' => PersonName::normalizeSuffix($this->input('suffix'))]);
    }

    /** The validation rules of the registration form. */
    public function rules(): array
    {
        return PersonName::rules() + [
            'email' => ['required', 'email:rfc', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ];
    }

    /** Plain messages for the name parts. */
    public function messages(): array
    {
        return PersonName::messages('your');
    }

    /** Field names in plain words. */
    public function attributes(): array
    {
        return PersonName::attributes();
    }
}
