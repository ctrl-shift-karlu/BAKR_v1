<?php

namespace App\Http\Requests;

use App\Models\ContactMessage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** The About page feedback form: a required 1-5 star rating, what was used, an optional comment. */
class SiteFeedbackRequest extends FormRequest
{
    /** Anyone may send feedback (the route is rate-limited to 5 per 10 minutes). */
    public function authorize(): bool
    {
        return true;
    }

    /** Errors go back to the form itself, not the top of the About page. */
    protected function getRedirectUrl(): string
    {
        return route('about').'#feedback';
    }

    /** Trims the text fields, lower-cases the e-mail and keeps only text values in the aspects list. */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
            'email' => strtolower(trim((string) $this->input('email'))),
            'message' => trim((string) $this->input('message')) ?: null,
            'aspects' => array_values(array_filter((array) $this->input('aspects', []), 'is_string')),
        ]);
    }

    /** The validation rules of the feedback form. */
    public function rules(): array
    {
        return [
            'rating' => ['required', 'integer', 'between:1,5'],
            'aspects' => ['array', 'max:'.count(ContactMessage::ASPECTS)],
            'aspects.*' => ['distinct', Rule::in(array_keys(ContactMessage::ASPECTS))],
            'name' => ['required', 'string', 'max:80'],
            'email' => ['required', 'email:rfc', 'max:120'],
            'message' => ['nullable', 'string', 'max:2000'],
            'share_publicly' => ['nullable', 'boolean'],
        ];
    }

    /** Friendlier error messages for the feedback form. */
    public function messages(): array
    {
        return [
            'rating.required' => 'Please choose a star rating from 1 to 5.',
            'rating.*' => 'Please choose a star rating from 1 to 5.',
            'aspects.*' => 'Please choose from the listed parts of BAKR.',
            'email.email' => 'Please enter a valid email address so we can reply.',
        ];
    }
}
