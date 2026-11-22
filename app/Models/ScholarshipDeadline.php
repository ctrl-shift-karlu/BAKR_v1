<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * When a scholarship's maintaining documents are due for ONE semester (owner, 2026-11-21). Set by the
 * scholarship's provider on the Requirements page; saving it opens that semester's checklist for the
 * scholarship's scholars and tells them (RequirementChecklist::setDeadline). Every checklist item of
 * that scholarship and semester that is not approved yet carries the same date (scholar_requirements.due_on).
 */
class ScholarshipDeadline extends Model
{
    protected $fillable = ['scholarship_id', 'term', 'due_on', 'set_by'];

    /** The due date as a Carbon date. */
    protected function casts(): array
    {
        return ['due_on' => 'date'];
    }

    /** The scholarship this deadline belongs to. */
    public function scholarship(): BelongsTo
    {
        return $this->belongsTo(Scholarship::class);
    }
}
