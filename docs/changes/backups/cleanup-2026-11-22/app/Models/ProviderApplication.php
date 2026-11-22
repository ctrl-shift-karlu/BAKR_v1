<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * A student chose to share one match with that scholarship's provider (explicit consent).
 * Only these students are visible to a provider as individuals; everyone else stays an aggregate.
 */
class ProviderApplication extends Model
{
    public const STATUSES = [
        'submitted' => 'New',
        'approved' => 'Approved',
        'rejected' => 'Not accepted',
        'withdrawn' => 'Withdrawn',
    ];

    protected $fillable = ['user_id', 'scholarship_id', 'status', 'snapshot', 'consented_at', 'decided_at', 'decided_by', 'decision_note'];

    protected function casts(): array
    {
        return [
            'snapshot' => 'encrypted:array',
            'consented_at' => 'datetime',
            'decided_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scholarship(): BelongsTo
    {
        return $this->belongsTo(Scholarship::class);
    }

    public function scholar(): HasOne
    {
        return $this->hasOne(Scholar::class);
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    /** The student's CURRENT match for this scholarship (live, so a re-match is reflected). */
    public function currentMatch(): ?ScholarshipMatch
    {
        return ScholarshipMatch::where('scholarship_id', $this->scholarship_id)
            ->whereHas('application', fn ($q) => $q->where('user_id', $this->user_id)->where('is_current', true))
            ->first();
    }
}
