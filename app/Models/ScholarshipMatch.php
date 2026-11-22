<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

/**
 * The result of one scholarship for one saved set of answers: the rule-check status, the AI review (when it
 * ran), the shown status (the stricter of the two), an internal score for sorting, and the explanation.
 */
class ScholarshipMatch extends Model
{
    public const STATUS_LABELS = [
        'eligible' => 'Eligible',
        'possible' => 'Nearly eligible',   // every checkable rule passes; something must be confirmed with the provider
        'needs_info' => 'Needs more information',
        'not_eligible' => 'Not eligible',
    ];

    /** Display order: best first. */
    public const STATUS_ORDER = ['eligible', 'possible', 'needs_info', 'not_eligible'];

    protected $fillable = [
        'scholarship_application_id', 'scholarship_id', 'status', 'rule_status', 'ai_status', 'ai_fit', 'ai_notes',
        'score', 'checks', 'explanation', 'explanation_source', 'explained_at',
    ];

    /** Fit the AI matcher gives a scholarship (sorting and a small label only, never a percentage). */
    public const FIT_LABELS = ['high' => 'Strong fit', 'medium' => 'Fair fit', 'low' => 'Weak fit'];

    /** JSON columns as arrays, the score as a number. */
    protected function casts(): array
    {
        return ['checks' => 'array', 'ai_notes' => 'array', 'score' => 'integer', 'explained_at' => 'datetime'];
    }

    /** Stricter of two statuses: not_eligible > needs_info > possible > eligible. */
    public static function stricter(string $a, ?string $b): string
    {
        if ($b === null || ! isset(self::STATUS_LABELS[$b])) {
            return $a;
        }
        $order = array_flip(self::STATUS_ORDER);

        return ($order[$b] ?? 0) > ($order[$a] ?? 0) ? $b : $a;
    }

    /** True when the Gemini matcher reviewed this match. */
    public function aiReviewed(): bool
    {
        return $this->ai_status !== null;
    }

    /** The AI matcher made the result stricter than the rule check alone. */
    public function aiLowered(): bool
    {
        return $this->aiReviewed() && $this->rule_status !== null && $this->status !== $this->rule_status;
    }

    /** @return list<string> */
    public function aiList(string $key): array
    {
        $notes = (array) ($this->ai_notes ?? []);
        // Reviews saved before 2026-11-19 called the reminders "missing".
        $list = $notes[$key] ?? ($key === 'reminders' ? ($notes['missing'] ?? []) : []);

        return array_values(array_filter((array) $list, 'is_string'));
    }

    /** The saved answers this result belongs to. */
    public function application(): BelongsTo
    {
        return $this->belongsTo(ScholarshipApplication::class, 'scholarship_application_id');
    }

    /** The scholarship this result is for. */
    public function scholarship(): BelongsTo
    {
        return $this->belongsTo(Scholarship::class);
    }

    /** Statuses shown as results. "Not a match right now" is left out of the lists. */
    public const LISTED = ['eligible', 'possible', 'needs_info'];

    /** The status in words: Eligible, Nearly eligible, Needs more information or Not eligible. */
    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    /** The required rules this match fails (each a check from RuleEvaluator). */
    public function failedRequired(): array
    {
        return array_values(array_filter((array) $this->checks, fn ($c) => ($c['result'] ?? null) === 'fail' && ! empty($c['required'])));
    }

    /** Not a match only because of exactly one required rule. */
    public function isNearMiss(): bool
    {
        return $this->status === 'not_eligible' && count($this->failedRequired()) === 1;
    }

    /** The one required rule a near miss fails, or null. */
    public function missingRequirement(): ?array
    {
        return $this->isNearMiss() ? $this->failedRequired()[0] : null;
    }

    /**
     * Near misses among $matches, closest first. Shown only when nothing matches, so a
     * student still sees which scholarships are one requirement away.
     */
    public static function nearMisses(Collection $matches): Collection
    {
        return $matches->filter(fn (self $m) => $m->isNearMiss())->sortByDesc('score')->values();
    }
}
