<?php

namespace App\Http\Requests;

use App\Matching\ApplicationSchema;
use App\Models\Program;
use App\Models\StudentConsent;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * Validation of the student's matching form: the rules come from ApplicationSchema (one source of truth),
 * plus the Privacy Policy agreement and the typed-name certification at the end of the form.
 */
class ScholarshipApplicationRequest extends FormRequest
{
    /** Only signed-in students reach this request. */
    public function authorize(): bool
    {
        return true;   // the auth + role:student middleware already guards the route
    }

    /** Sets the year level to 1 for an incoming first-year (the field is hidden for them). */
    protected function prepareForValidation(): void
    {
        // Incoming first-years are always year 1, so the field is hidden for them.
        if ($this->input('enrollment_status') === 'incoming_first_year') {
            $this->merge(['year_level' => '1']);
        }
    }

    /** The ApplicationSchema rules plus the Privacy Policy agreement and the certification name. */
    public function rules(): array
    {
        $rules = ApplicationSchema::rules();
        // Agreement to the Privacy Policy (Data Privacy Act of 2012) at the end of the form, on EVERY submit (2026-11-21).
        $rules['privacy_consent'] = ['accepted'];
        // Certification: the student types their full name as on their account. It is checked, never stored.
        $rules['certify_name'] = ['required', 'string', 'max:255'];

        return $rules;
    }

    /** Plain-language error messages shown next to the questions. */
    public function messages(): array
    {
        return [
            'required' => 'Please answer this question.',
            'required_if' => 'Please answer this question.',
            'in' => 'Please choose one of the options.',
            'between' => 'That number looks out of range. Please check it.',
            'birth_date.before_or_equal' => 'Please check your date of birth.',
            'birth_date.after' => 'Please check your date of birth.',
            'birth_date.date' => 'Please enter a valid date.',
            'privacy_consent.accepted' => 'Please read and agree to the Privacy Policy so you can continue.',
            'certify_name.required' => 'Type your full name to certify that your answers are true.',
            // Every tick-box group is required and has "None of these" (2026-11-21); min:1 is used only on those groups.
            'min' => 'Pick at least one option. Choose "None of these" if none applies to you.',
            'college_gwa_scale.required_if' => 'Choose the scale your GWA is written in.',
            'college_gwa_scale.required_with' => 'Choose the scale your GWA is written in.',
        ];
    }

    /**
     * Extra checks after the rules: the certification name must match the account name, the program must be
     * offered at the chosen campus, "awards" must be answered once a real talent is ticked, and the GWA must fit its scale.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $typed = $this->input('certify_name');
            if (is_string($typed) && $typed !== '' && ! self::sameName($typed, (string) $this->user()?->name)) {
                $v->errors()->add('certify_name', 'Type your full name exactly as it appears on your account: '.$this->user()?->name.'.');
            }

            // The program must be one that the chosen campus offers (the form only lists those; this stops a hand-made request).
            $campus = $this->input('campus_id');
            $program = $this->input('program_id');
            if (filled($campus) && filled($program) && ! $v->errors()->has('program_id')
                && ! Program::where('id', $program)->where('campus_id', $campus)->exists()) {
                $v->errors()->add('program_id', 'Your campus doesn\'t offer that program. Please pick one from the list.');
            }

            // "Do you have awards in that skill?" is asked once a real skill is ticked (ApplicationSchema 'when' => 'talent').
            if (ApplicationSchema::applies('talent', ['talent_categories' => (array) $this->input('talent_categories', [])])
                && blank($this->input('has_awards'))) {
                $v->errors()->add('has_awards', 'Please answer this question.');
            }

            $gwa = $this->input('college_gwa');
            if (! is_numeric($gwa)) {
                return;
            }
            if ($this->input('college_gwa_scale') === 'dnsc' && ($gwa < 1 || $gwa > 5)) {
                $v->errors()->add('college_gwa', 'On the 1.0 to 5.0 scale, enter a number from 1.0 to 5.0.');
            }
            if ($this->input('college_gwa_scale') !== 'dnsc' && $gwa < 60) {
                $v->errors()->add('college_gwa', 'As a percentage, enter a number from 60 to 100. Choose the 1.0 to 5.0 scale if that is how you are graded.');
            }
        });
    }

    /** Same name, ignoring letter case and extra spaces. */
    public static function sameName(string $a, string $b): bool
    {
        $norm = fn (string $s) => mb_strtolower(trim(preg_replace('/\s+/u', ' ', $s)));

        return $norm($a) !== '' && $norm($a) === $norm($b);
    }

    /** True when the student already agreed to the current Privacy Policy on an earlier submit. */
    public function hasConsent(): bool
    {
        return $this->user() !== null && StudentConsent::where('user_id', $this->user()->id)
            ->where('consent_type', StudentConsent::TYPE_DATA_PRIVACY)->exists();
    }

    /** Only schema fields, never anything else the browser sent. */
    public function answers(): array
    {
        return $this->safe()->only(array_keys(ApplicationSchema::fields()));
    }
}
