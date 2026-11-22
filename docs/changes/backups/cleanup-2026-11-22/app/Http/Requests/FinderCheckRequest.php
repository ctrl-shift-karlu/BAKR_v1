<?php

namespace App\Http\Requests;

use App\Matching\ApplicationSchema;

/**
 * Public Scholarship Finder: the same questions and validation as the student questionnaire
 * (ApplicationSchema), but nothing is stored, so no data-privacy consent box is needed here.
 */
class FinderCheckRequest extends ScholarshipApplicationRequest
{
    public function rules(): array
    {
        return ApplicationSchema::rules();
    }
}
