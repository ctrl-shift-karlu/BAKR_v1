<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Audit trail: every provider edit of a scholarship, with the full before/after values. */
class ScholarshipRevision extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['scholarship_id', 'user_id', 'before', 'after', 'rules_changed'];

    protected function casts(): array
    {
        return ['before' => 'array', 'after' => 'array', 'rules_changed' => 'boolean', 'created_at' => 'datetime'];
    }

    public function scholarship(): BelongsTo
    {
        return $this->belongsTo(Scholarship::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
