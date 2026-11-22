<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * One saved version of a student's answers. BAKR uses it only to match scholarships;
 * it is not an application to any scholarship provider.
 */
class ScholarshipApplication extends Model
{
    protected $fillable = [
        'user_id', 'version', 'is_current', 'status', 'answers', 'ai_summary', 'ai_summary_at',
        'ai_match_status', 'ai_matched_at', 'ai_model', 'submitted_at',
    ];

    /** ai_match_status values (see migration 2026_11_19_000002_add_ai_matching). */
    public const AI_PENDING = 'pending';

    public const AI_DONE = 'done';

    public const AI_FAILED = 'failed';

    public const AI_OFF = 'off';

    protected function casts(): array
    {
        return [
            'answers' => 'encrypted:array',
            'is_current' => 'boolean',
            'ai_summary_at' => 'datetime',
            'ai_matched_at' => 'datetime',
            'submitted_at' => 'datetime',
        ];
    }

    public function aiPending(): bool
    {
        return $this->ai_match_status === self::AI_PENDING;
    }

    public function aiDone(): bool
    {
        return $this->ai_match_status === self::AI_DONE;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function matches(): HasMany
    {
        return $this->hasMany(ScholarshipMatch::class);
    }

    /** Reference number shown on the form and its printable copy, e.g. BAKR-2026-000123. Derived, not stored. */
    public function referenceNumber(): string
    {
        return 'BAKR-'.($this->submitted_at ?? $this->created_at ?? now())->format('Y').'-'.str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }
}
