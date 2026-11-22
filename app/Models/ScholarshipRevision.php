<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Audit trail: every provider edit of a scholarship, with the full before/after values. */
class ScholarshipRevision extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['scholarship_id', 'user_id', 'before', 'after', 'rules_changed'];

    /** The before / after snapshots as arrays. */
    protected function casts(): array
    {
        return ['before' => 'array', 'after' => 'array', 'rules_changed' => 'boolean', 'created_at' => 'datetime'];
    }

    /** The scholarship that was edited. */
    public function scholarship(): BelongsTo
    {
        return $this->belongsTo(Scholarship::class);
    }

    /** The provider account that made the edit. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
