<?php

namespace App\Models;

use App\Matching\ProgramCatalog;
use App\Support\AcademicTerm;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * A student who currently holds (or held) one of the provider's scholarships.
 * Linked to a BAKR SCHOLAR account (users.role = scholar, created when the provider adds them;
 * separate from the student/matching accounts), or an older roster entry without an account
 * (user_id null, full_name/email). Use displayName() / contactEmail() in views, never
 * ->user->name, because roster scholars may have no user.
 */
class Scholar extends Model
{
    /**
     * The three statuses a provider sees (owner's decision 2026-11-18):
     *   active      holds the scholarship and is up to date
     *   inactive    either AUTOMATIC: an active scholar with a maintaining document still not submitted
     *               (or sent back) after its due date, for providers that collect documents in BAKR
     *               (clears by itself once the scholar submits); or MANUAL (status = inactive): set by
     *               a provider that collects documents in person at the DNSC office (owner, 2026-11-19)
     *   terminated  ended by the provider (every provider, for its own scholars only)
     */
    public const STATUSES = [
        'active' => 'Active',
        'inactive' => 'Inactive',
        'terminated' => 'Terminated',
    ];

    /** What the `status` column may hold. "inactive" is stored only when a provider without BAKR documents sets it. */
    public const STORED_STATUSES = ['active', 'inactive', 'terminated'];

    /** Still holding the scholarship (Active or Inactive): what "current scholars" counts. */
    public const CURRENT = ['active', 'inactive'];

    /** Pill tone per status, for every view that shows one. */
    public const STATUS_TONES = ['active' => 'is-ok', 'inactive' => 'is-warn', 'terminated' => 'is-danger'];

    /** Requirement states that count as "not submitted" once the due date has passed. */
    public const OVERDUE_REQUIREMENT_STATES = ['missing', 'needs_revision'];

    /** Only these stored statuses get stipends and per-term requirement checklists. */
    public const GOOD_STANDING = ['active'];

    protected $fillable = [
        'user_id', 'full_name', 'email', 'scholarship_id', 'scholar_code', 'student_number',
        'birthdate', 'gender', 'mobile', 'address',
        'program', 'campus', 'year_level', 'status', 'grant_type', 'started_term', 'stipend_amount', 'added_by', 'credentials_sent_at',
    ];

    /** Year levels a scholar can be in (owner's decision 2026-11-17: no 5th year). */
    public const YEAR_LEVELS = ['1' => '1st year', '2' => '2nd year', '3' => '3rd year', '4' => '4th year'];

    /** Gender, as the provider records it (owner, 2026-11-20: Male or Female only). */
    public const GENDERS = ['male' => 'Male', 'female' => 'Female'];

    /** Longest Scholar ID a provider can type. */
    public const SCHOLAR_CODE_MAX = 40;

    /** Column types: stipend as a 2-decimal amount, dates as Carbon objects. */
    protected function casts(): array
    {
        return ['stipend_amount' => 'decimal:2', 'credentials_sent_at' => 'datetime', 'birthdate' => 'date'];
    }

    /** The scholar's BAKR login (null for older roster rows without an account). */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** The scholarship this scholar holds. */
    public function scholarship(): BelongsTo
    {
        return $this->belongsTo(Scholarship::class);
    }

    /** The scholar's maintaining-document checklist items, every semester. */
    public function requirements(): HasMany
    {
        return $this->hasMany(ScholarRequirement::class);
    }

    /** The scholar's stipend records, every semester. */
    public function disbursements(): HasMany
    {
        return $this->hasMany(StipendDisbursement::class);
    }

    /** Correction requests the scholar sent to this scholarship's provider (ScholarMessage). */
    public function messages(): HasMany
    {
        return $this->hasMany(ScholarMessage::class);
    }

    /**
     * The campus the scholar studies at: the one the provider recorded, else (older rows) the only
     * campus that offers their program, else null (a program several campuses offer cannot be placed).
     */
    public function campusLabel(): ?string
    {
        return $this->campus ?: ProgramCatalog::inferCampus($this->program);
    }

    /** The account's name, else the roster name. */
    public function displayName(): string
    {
        return $this->user?->name ?? ($this->full_name ?: 'Unnamed scholar');
    }

    /** The account e-mail, else the roster e-mail (null when neither is known). */
    public function contactEmail(): ?string
    {
        return $this->user?->email ?? $this->email;
    }

    /** Up to two initials for avatars. */
    public function initials(): string
    {
        return collect(explode(' ', $this->displayName()))->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');
    }

    /**
     * Why this scholar's stipend for $term cannot be released yet, or null when it can
     * (owner's rule 2026-11-18): a stipend is released only after EVERY maintaining requirement of
     * that semester is APPROVED by the provider. Providers that don't collect requirements in BAKR
     * are not blocked, and neither is a scholarship that lists no maintaining documents at all.
     * Needs `scholarship`; uses the loaded `requirements` when present.
     */
    public function releaseBlocker(string $term): ?string
    {
        return $this->releaseBlock($term)[1] ?? null;
    }

    /**
     * releaseBlocker() with the reason's kind, so the provider can be told what to do:
     * [terminated | inactive | not_started | no_deadline | not_listed | not_approved, text], or null.
     */
    public function releaseBlock(string $term): ?array
    {
        // Terminated means no more stipends, including ones prepared before (owner, 2026-11-18).
        if ($this->isTerminated()) {
            return ['terminated', 'the scholarship was terminated'];
        }
        if ($this->isMarkedInactive()) {
            return ['inactive', 'the scholar is marked inactive'];
        }
        // No stipend for a semester before the scholar started (owner, 2026-11-21).
        if (! $this->startedBy($term)) {
            return ['not_started', "the scholar only started in {$this->started_term}"];
        }

        $scholarship = $this->scholarship;
        if (! $scholarship || ! $scholarship->offers('requirements')) {
            return null;
        }

        $rows = $this->relationLoaded('requirements')
            ? $this->requirements->where('term', $term)
            : $this->requirements()->where('term', $term)->get();

        // "Only if it applies" documents (is_optional) never hold a stipend back (owner, 2026-11-19).
        $rows = $rows->reject(fn ($r) => $r->is_optional);

        if ($rows->isEmpty()) {
            if (! $scholarship->hasRequiredMaintainingDocuments()) {
                return null;
            }

            return $scholarship->deadlineFor($term)
                ? ['not_listed', "the {$term} maintaining documents are not on this scholar's checklist yet"]
                : ['no_deadline', "the deadline for the {$term} maintaining documents has not been set yet"];
        }

        $open = $rows->where('status', '!=', 'approved')->count();

        return $open ? ['not_approved', "{$open} of {$rows->count()} requirements for {$term} not approved yet"] : null;
    }

    /**
     * Had the scholar started by $term? ("Scholar since", owner 2026-11-21.) They get no checklist and no stipend
     * for a semester before it. A row without a start semester (only possible for rows made outside the app)
     * is not limited. Same rule as scopeStartedBy().
     */
    public function startedBy(string $term): bool
    {
        return $this->started_term === null || in_array($this->started_term, AcademicTerm::upTo($term), true);
    }

    /** Scholars who had started by $term (see startedBy()). */
    public function scopeStartedBy(Builder $query, string $term): Builder
    {
        $column = $query->getModel()->qualifyColumn('started_term');

        return $query->where(fn ($q) => $q->whereNull($column)->orWhereIn($column, AcademicTerm::upTo($term)));
    }

    /** The scholarship's published option this scholar holds, if any. */
    public function grant(): ?array
    {
        return $this->grant_type ? ($this->scholarship?->grantOptions()[$this->grant_type] ?? null) : null;
    }

    /** "Half scholarship (Half-SSP)" — only when the scholarship has more than one type. */
    public function grantLabel(): ?string
    {
        return $this->scholarship?->hasGrantTypes() ? ($this->grant()['label'] ?? null) : null;
    }

    /** The year level in words, e.g. "2nd year" (null when not recorded). */
    public function yearLabel(): ?string
    {
        return $this->year_level === null ? null : (self::YEAR_LEVELS[$this->year_level] ?? 'Year '.$this->year_level);
    }

    /** The gender in words ("Male" / "Female"), or null when not recorded. */
    public function genderLabel(): ?string
    {
        return $this->gender ? (self::GENDERS[$this->gender] ?? null) : null;
    }

    /** "Mar 14, 2005". */
    public function birthdateLabel(): ?string
    {
        return $this->birthdate?->format('M j, Y');
    }

    /** "0917 123 4567" (stored as 09171234567). */
    public function mobileLabel(): ?string
    {
        $m = (string) $this->mobile;

        return preg_match('/^(\d{4})(\d{3})(\d{4})$/', $m, $p) ? "{$p[1]} {$p[2]} {$p[3]}" : ($m ?: null);
    }

    /**
     * A typed Scholar ID as stored (owner, 2026-11-20: no format, the provider types it as their office
     * writes it): trimmed, inner spaces collapsed; null when empty. Uniqueness within a scholarship
     * ignores capitals (ScholarRoster::scholarCodeError).
     */
    public static function normalizeCode(?string $code): ?string
    {
        $code = trim(preg_replace('/\s+/u', ' ', (string) $code));

        return $code === '' ? null : $code;
    }

    /**
     * The scholar's BAKR login, for the provider:
     *   none      no account (older roster rows from before accounts were created on add)
     *   not_sent  account has a temporary password, but the login e-mail did not go out
     *   waiting   login e-mailed; the scholar has not signed in and changed the password yet
     *   active    the scholar has set their own password (or used their existing account)
     */
    public function accountState(): string
    {
        return match (true) {
            $this->user === null => 'none',
            ! $this->user->must_change_password => 'active',
            $this->credentials_sent_at === null => 'not_sent',
            default => 'waiting',
        };
    }

    /** Can the provider send new login details? Only while the temporary password is still unused. */
    public function canResendLogin(): bool
    {
        return in_array($this->accountState(), ['not_sent', 'waiting'], true);
    }

    /** Constrains a requirements query to items still not submitted after their due date. */
    public static function overdueConstraint(): \Closure
    {
        return fn ($q) => $q->where('is_optional', false)
            ->whereIn('status', self::OVERDUE_REQUIREMENT_STATES)
            ->whereNotNull('due_on')
            ->whereDate('due_on', '<', now()->toDateString());
    }

    /** Adds `has_overdue` to each row, so standing() needs no extra query in lists. */
    public function scopeWithStanding(Builder $query): Builder
    {
        return $query->withExists(['requirements as has_overdue' => self::overdueConstraint()]);
    }

    /** Filters by the provider-facing status (active / inactive / terminated). */
    public function scopeWhereStanding(Builder $query, string $standing): Builder
    {
        return match ($standing) {
            'terminated' => $query->where('status', 'terminated'),
            // Marked inactive by the provider, or automatically (overdue maintaining documents).
            'inactive' => $query->where(fn ($w) => $w->where('status', 'inactive')
                ->orWhere(fn ($a) => $a->where('status', 'active')->whereHas('requirements', self::overdueConstraint()))),
            'active' => $query->where('status', 'active')->whereDoesntHave('requirements', self::overdueConstraint()),
            default => $query,
        };
    }

    /** Active, inactive (marked by the provider, or overdue maintaining documents) or terminated. */
    public function standing(): string
    {
        if (in_array($this->status, ['terminated', 'inactive'], true)) {
            return $this->status;
        }
        if (! $this->relationLoaded('requirements') && array_key_exists('has_overdue', $this->attributes)) {
            return $this->attributes['has_overdue'] ? 'inactive' : 'active';
        }

        return $this->overdueRequirements()->isNotEmpty() ? 'inactive' : 'active';
    }

    /**
     * The requirements past their due date and still not submitted. Uses the loaded `requirements`
     * when present; otherwise queries (skipped when withStanding() already said there are none).
     */
    public function overdueRequirements(): Collection
    {
        if ($this->status === 'terminated') {
            return collect();
        }
        if ($this->relationLoaded('requirements')) {
            return $this->requirements->filter(fn (ScholarRequirement $r) => $r->isOverdue())->values();
        }
        if (array_key_exists('has_overdue', $this->attributes) && ! $this->attributes['has_overdue']) {
            return collect();
        }

        return $this->requirements()->where(self::overdueConstraint())->get();
    }

    /** The provider-facing status in words: Active, Inactive or Terminated. */
    public function statusLabel(): string
    {
        return self::STATUSES[$this->standing()];
    }

    /** The CSS tone class of the status (is-ok, is-warn, is-danger). */
    public function statusTone(): string
    {
        return self::STATUS_TONES[$this->standing()];
    }

    /** True when the provider ended this scholarship (no uploads, no stipends). */
    public function isTerminated(): bool
    {
        return $this->status === 'terminated';
    }

    /** Marked inactive by hand (a provider that collects documents in person), not by overdue documents. */
    public function isMarkedInactive(): bool
    {
        return $this->status === 'inactive';
    }

    /** True for an Active stored status: only these scholars get checklists and stipends. */
    public function isInGoodStanding(): bool
    {
        return in_array($this->status, self::GOOD_STANDING, true);
    }
}
