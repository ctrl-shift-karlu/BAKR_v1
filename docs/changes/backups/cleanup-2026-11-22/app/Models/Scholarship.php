<?php

namespace App\Models;

use App\Services\Provider\ProviderScope;
use App\Support\MaintainingDocument;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Scholarship extends Model
{
    public const SECTORS = [
        'internal' => 'DNSC Scholarship',
        'government' => 'Government',
        'private' => 'Private / Foundation',
    ];

    protected $fillable = [
        'slug', 'name', 'sector', 'provider', 'official_url', 'overview', 'benefits', 'grants', 'documents', 'renewal_requirements',
        'application_route', 'window_note', 'source_note', 'rules', 'is_active', 'sort_order', 'verified_on',
        'application_opens_on', 'application_closes_on', 'results_on',
    ];

    protected function casts(): array
    {
        return [
            'benefits' => 'array', 'grants' => 'array', 'documents' => 'array', 'rules' => 'array', 'renewal_requirements' => 'array',
            'is_active' => 'boolean', 'verified_on' => 'date',
            'application_opens_on' => 'date', 'application_closes_on' => 'date', 'results_on' => 'date',
        ];
    }

    /**
     * Where the current call stands, from the provider's dates: open | upcoming | closed | null (no dates published).
     * Dates are whole days in the app timezone; the closing day itself still counts as open.
     */
    public function callStatus(?CarbonInterface $today = null): ?string
    {
        $today = ($today ?? now())->copy()->startOfDay();
        $opens = $this->application_opens_on;
        $closes = $this->application_closes_on;

        return match (true) {
            $opens === null && $closes === null => null,
            $opens !== null && $today->lt($opens) => 'upcoming',
            $closes !== null && $today->gt($closes) => 'closed',
            default => 'open',
        };
    }

    /** Short words for the call, e.g. "Open, closes Jul 17 (in 5 days)", "Opens Jun 22, 2026", "Closed Jul 17, 2026". Null when no dates. */
    public function callLabel(?CarbonInterface $today = null): ?string
    {
        $today = ($today ?? now())->copy()->startOfDay();

        return match ($this->callStatus($today)) {
            'upcoming' => 'Opens '.$this->application_opens_on->format('M j, Y'),
            'closed' => 'Closed '.$this->application_closes_on->format('M j, Y'),
            'open' => $this->application_closes_on === null ? 'Open now' : (function () use ($today) {
                $days = (int) $today->diffInDays($this->application_closes_on);

                return 'Open, closes '.$this->application_closes_on->format('M j').' ('.match ($days) {
                    0 => 'today', 1 => 'tomorrow', default => "in {$days} days"
                }.')';
            })(),
            default => null,
        };
    }

    public function matches(): HasMany
    {
        return $this->hasMany(ScholarshipMatch::class);
    }

    public function scholars(): HasMany
    {
        return $this->hasMany(Scholar::class);
    }

    /** The maintaining-documents deadline of each semester (one row per semester). */
    public function deadlines(): HasMany
    {
        return $this->hasMany(ScholarshipDeadline::class);
    }

    /** The deadline set for $term, or null when the provider has not set one yet (uses the loaded `deadlines`). */
    public function deadlineFor(string $term): ?ScholarshipDeadline
    {
        return $this->deadlines->firstWhere('term', $term);
    }

    public function providerApplications(): HasMany
    {
        return $this->hasMany(ProviderApplication::class);
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(ScholarshipRevision::class);
    }

    /**
     * Qualifications for display, taken verbatim from the rules JSON (label, required,
     * confirm_note, any_of sub-labels). Nothing is written by hand anywhere else.
     *
     * @return list<array{label: string, required: bool, confirm: bool, note: ?string, any_of: list<string>}>
     */
    public function qualifications(): array
    {
        $rules = array_filter($this->rules ?? [], fn ($r) => is_array($r) && ($r['label'] ?? '') !== '');

        return array_values(array_map(fn (array $rule) => [
            'label' => (string) $rule['label'],
            'required' => (bool) ($rule['required'] ?? false),
            'confirm' => ($rule['type'] ?? null) === 'confirm',
            'note' => $rule['confirm_note'] ?? null,
            'any_of' => ($rule['type'] ?? null) === 'any_of'
                ? array_values(array_map(fn ($r) => (string) ($r['label'] ?? ''), $rule['rules'] ?? []))
                : [],
        ], $rules));
    }

    /**
     * Published stipend options (config 'grants'): [key => {key, label, amount, per}].
     * Empty = no fixed published amount. More than one = the scholar has a type (e.g. CMSP Full / Half).
     *
     * @return array<string, array{key: string, label: string, amount: int|float, per: string}>
     */
    public function grantOptions(): array
    {
        $out = [];
        foreach ($this->grants ?? [] as $g) {
            if (is_array($g) && ($g['key'] ?? '') !== '') {
                $out[$g['key']] = $g + ['label' => $g['key'], 'amount' => null, 'per' => 'semester'];
            }
        }

        return $out;
    }

    /** True when the scholar must be given a type (Full / Half, TES category…). */
    public function hasGrantTypes(): bool
    {
        return count($this->grantOptions()) > 1;
    }

    /** "₱40,000 per semester" for one option, or null. */
    public static function grantAmountLabel(?array $grant): ?string
    {
        if (! $grant || $grant['amount'] === null) {
            return null;
        }

        return '₱'.number_format((float) $grant['amount'], (float) $grant['amount'] == (int) $grant['amount'] ? 0 : 2).' per '.($grant['per'] === 'month' ? 'month' : 'semester');
    }

    /** Does this scholarship's provider use the optional portal page ('disbursements' | 'requirements')? */
    public function offers(string $feature): bool
    {
        return ProviderScope::scholarshipHas($this->slug, $feature);
    }

    /**
     * Where "Official website" links go: the scholarship's own official page, else its provider's official site.
     * Only http(s) links, so a stored value can never become a javascript: link.
     */
    public function officialWebsite(): ?string
    {
        foreach ([$this->official_url, ProviderScope::accountFor((string) $this->slug)['website'] ?? null] as $url) {
            if (is_string($url) && preg_match('#^https?://#i', $url)) {
                return $url;
            }
        }

        return null;
    }

    /**
     * The maintaining documents scholars submit each semester, each as
     * {name, note, types, max_mb, required} (App\Support\MaintainingDocument).
     */
    public function maintainingDocuments(): array
    {
        return MaintainingDocument::list($this->renewal_requirements);
    }

    /** One maintaining document by its name, or null. */
    public function maintainingDocument(string $name): ?array
    {
        return collect($this->maintainingDocuments())->firstWhere('name', $name);
    }

    /** Only the documents every scholar must submit (they decide Inactive and stipend release). */
    public function hasRequiredMaintainingDocuments(): bool
    {
        return collect($this->maintainingDocuments())->contains('required', true);
    }

    /**
     * Short codes (owner, 2026-11-18): the official abbreviation where one exists, and "DNSC A/B/C"
     * for the DNSC categories. Keyed by slug, which never changes (names can be edited by providers).
     */
    public const SHORT_NAMES = [
        'dnsc-academic' => 'DNSC Academic',
        'dnsc-gia-a' => 'DNSC A',          // Grant-in-Aid, Category A
        'dnsc-gia-b' => 'DNSC B',          // Grant-in-Aid, Category B
        'dnsc-sa-c' => 'DNSC C',           // Student Assistant, Category C
        'ched-tdp' => 'TDP',
        'ched-tes' => 'TES',
        'ched-cmsp' => 'CMSP',
        'bpmsp' => 'BPMSP',
        'dost-sei' => 'DOST-SEI',
        'grabscholar' => 'GrabScholar',
        'cebuana-clfi' => 'CLFI',          // Cebuana Lhuillier Foundation, Inc.
        'landbank-ilp' => 'ILP',           // LANDBANK Scholarship Program
        'coscho' => 'CoScho',              // Scholarship Program for Coconut Farmers and Their Families
        'bfar-fsp' => 'FSP',               // BFAR Fisheries Scholarship Program
    ];

    /** Short code for badges and pills: SHORT_NAMES, else the text in trailing brackets, else initials. */
    public function shortName(): string
    {
        if (isset(self::SHORT_NAMES[$this->slug])) {
            return self::SHORT_NAMES[$this->slug];
        }
        if (preg_match('/\(([^)]+)\)\s*$/', $this->name, $m)) {
            return $m[1];
        }

        return collect(explode(' ', $this->name))->filter()->take(3)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');
    }

    /**
     * Logo everywhere a scholarship is shown: the logo of the provider account that runs it
     * (config/providers.php is the ONE place logos are set). No account or no logo file yet → BAKR logo.
     */
    public function logoPath(): string
    {
        return ProviderScope::accountFor($this->slug)['logo'] ?? 'images/logo/bakr-logo.png';
    }

    /** Cover photo for dashboard cards, by sector. */
    public function photoPath(): string
    {
        return 'images/img/'.match ($this->sector) {
            'internal' => 'dnsc_campus.jpg',
            'government' => 'graduates.jpg',
            default => 'dnsc_gate.jpg',
        };
    }

    public function sectorLabel(): string
    {
        return self::SECTORS[$this->sector] ?? ucfirst($this->sector);
    }
}
