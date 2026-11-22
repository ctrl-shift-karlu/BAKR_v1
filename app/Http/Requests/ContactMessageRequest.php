<?php

namespace App\Http\Requests;

use App\Models\ContactMessage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Validation of the public Contact form (topic, name, e-mail, message; rating and sharing only for feedback). */
class ContactMessageRequest extends FormRequest
{
    /** Anyone may send a message (the route is rate-limited to 5 per 10 minutes). */
    public function authorize(): bool
    {
        return true;
    }

    /** Trims the text fields and lower-cases the e-mail before the rules run. */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
            'email' => strtolower(trim((string) $this->input('email'))),
            'message' => trim((string) $this->input('message')),
            'rating' => $this->filled('rating') ? $this->input('rating') : null,
        ]);
    }

    /** The validation rules of the Contact form. */
    public function rules(): array
    {
        return [
            'topic' => ['required', Rule::in(array_keys(ContactMessage::TOPICS))],
            'name' => ['required', 'string', 'max:80'],
            'email' => ['required', 'email:rfc', 'max:120'],
            'message' => ['required', 'string', 'min:10', 'max:2000'],
            'rating' => ['nullable', 'integer', 'between:1,5'],
            'share_publicly' => ['nullable', 'boolean'],
        ];
    }

    /** Friendlier error messages for the Contact form. */
    public function messages(): array
    {
        return [
            'topic.required' => 'Please choose what your message is about.',
            'message.min' => 'Please write at least a short sentence so we can help.',
            'email.email' => 'Please enter a valid email address so we can reply.',
        ];
    }
}
