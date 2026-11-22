<?php

namespace App\Http\Requests;

use App\Models\ContactMessage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ContactMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
            'email' => strtolower(trim((string) $this->input('email'))),
            'message' => trim((string) $this->input('message')),
            'rating' => $this->filled('rating') ? $this->input('rating') : null,
        ]);
    }

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

    public function messages(): array
    {
        return [
            'topic.required' => 'Please choose what your message is about.',
            'message.min' => 'Please write at least a short sentence so we can help.',
            'email.email' => 'Please enter a valid email address so we can reply.',
        ];
    }
}
