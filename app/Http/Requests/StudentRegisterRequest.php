<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

/** Validation of the student registration form: name, a unique e-mail and a password of at least 8 characters with letters and numbers. */
class StudentRegisterRequest extends FormRequest
{
    /** Anyone may register a student account. */
    public function authorize(): bool
    {
        return true; // public registration for the matching platform
    }

    /** The validation rules of the registration form. */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email:rfc', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ];
    }
}
