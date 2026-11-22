<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * One stipend release for a scholar: either for the whole semester (release_type "semester",
 * period_month "") or for one month of it (release_type "monthly", period_month "YYYY-MM").
 */
class StipendDisbursement extends Model
{
    public const STATUSES = [
        'pending' => 'Not yet released',
        'released' => 'Released',
    ];

    public const RELEASE_TYPES = [
        'semester' => 'Per semester',
        'monthly' => 'Per month',
    ];

    protected $fillable = ['scholar_id', 'term', 'release_type', 'period_month', 'amount', 'status', 'released_at', 'reference', 'recorded_by'];

    protected $attributes = ['release_type' => 'semester', 'period_month' => ''];

    /** The amount as a 2-decimal value, the release date as a Carbon object. */
    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'released_at' => 'datetime'];
    }

    /** The scholar this stipend is for. */
    public function scholar(): BelongsTo
    {
        return $this->belongsTo(Scholar::class);
    }

    /** The status in words: "Not yet released" or "Released". */
    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    /** How the stipend is released, in words: "Per semester" or "Per month". */
    public function releaseTypeLabel(): string
    {
        return self::RELEASE_TYPES[$this->release_type] ?? ucfirst((string) $this->release_type);
    }

    /** "Whole semester" or the month, e.g. "Aug 2026". */
    public function periodLabel(): string
    {
        if ($this->release_type !== 'monthly' || ! preg_match('/^\d{4}-\d{2}$/', (string) $this->period_month)) {
            return 'Whole semester';
        }

        return Carbon::createFromFormat('!Y-m', $this->period_month)->format('M Y');
    }
}
