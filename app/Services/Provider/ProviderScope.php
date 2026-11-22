<?php

namespace App\Services\Provider;

use App\Models\Scholar;
use App\Models\ScholarMessage;
use App\Models\ScholarRequirement;
use App\Models\Scholarship;
use App\Models\StipendDisbursement;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Everything a provider may see, derived ONLY from the signed-in user's provider_key.
 * Every provider query goes through here, and every record ID that comes from a URL is
 * looked up inside this scope, so another provider's record is simply "not found" (404).
 */
class ProviderScope
{
    /** Portal pages a provider may or may not use (config/providers.php 'features'). Everything else is always on. */
    public const OPTIONAL_FEATURES = ['disbursements', 'requirements'];

    private ?Collection $scholarships = null;

    public function __construct(public readonly string $key, public readonly array $provider) {}

    /** Does this provider use the optional page? A provider without a 'features' list has all of them. */
    public function has(string $feature): bool
    {
        return self::providerHas($this->provider, $feature);
    }

    /** Same check by scholarship slug (for the student side). Scholarships no provider account owns keep every feature. */
    public static function scholarshipHas(string $slug, string $feature): bool
    {
        $provider = self::accountFor($slug);

        return $provider === null || self::providerHas($provider, $feature);
    }

    /** The provider account (config/providers.php) that runs this scholarship, or null. */
    public static function accountFor(string $slug): ?array
    {
        foreach (config('providers.accounts', []) as $provider) {
            if (in_array($slug, $provider['slugs'] ?? [], true)) {
                return $provider;
            }
        }

        return null;
    }

    private static function providerHas(array $provider, string $feature): bool
    {
        if (! in_array($feature, self::OPTIONAL_FEATURES, true)) {
            return true;
        }

        return in_array($feature, $provider['features'] ?? self::OPTIONAL_FEATURES, true);
    }

    public static function for(User $user): self
    {
        $key = (string) $user->provider_key;
        $provider = config('providers.accounts.'.$key);
        abort_unless($user->role === 'provider' && $key !== '' && is_array($provider), 403);

        return new self($key, $provider);
    }

    public function name(): string
    {
        return $this->provider['name'];
    }

    /** The provider's own official website (config/providers.php 'website'); null when none is set. */
    public function website(): ?string
    {
        $url = $this->provider['website'] ?? null;

        return is_string($url) && str_starts_with($url, 'https://') ? $url : null;
    }

    /** The provider's active scholarships, in catalogue order. */
    public function scholarships(): Collection
    {
        return $this->scholarships ??= Scholarship::query()
            ->whereIn('slug', $this->provider['slugs'])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    public function scholarshipIds(): array
    {
        return $this->scholarships()->pluck('id')->all();
    }

    public function scholarship(int $id): Scholarship
    {
        return $this->scholarships()->firstWhere('id', $id) ?? abort(404);
    }

    public function scholars(): Builder
    {
        return Scholar::query()->whereIn('scholarship_id', $this->scholarshipIds());
    }

    public function scholar(int $id): Scholar
    {
        return $this->scholars()->whereKey($id)->firstOrFail();
    }

    public function requirements(): Builder
    {
        return ScholarRequirement::query()->whereHas('scholar', fn ($q) => $q->whereIn('scholarship_id', $this->scholarshipIds()));
    }

    public function requirement(int $id): ScholarRequirement
    {
        return $this->requirements()->whereKey($id)->firstOrFail();
    }

    public function disbursements(): Builder
    {
        return StipendDisbursement::query()->whereHas('scholar', fn ($q) => $q->whereIn('scholarship_id', $this->scholarshipIds()));
    }

    public function disbursement(int $id): StipendDisbursement
    {
        return $this->disbursements()->whereKey($id)->firstOrFail();
    }

    /** Correction requests from this provider's own scholars (never another provider's, never the admin's inbox). */
    public function messages(): Builder
    {
        return ScholarMessage::query()->whereHas('scholar', fn ($q) => $q->whereIn('scholarship_id', $this->scholarshipIds()));
    }

    public function message(int $id): ScholarMessage
    {
        return $this->messages()->whereKey($id)->firstOrFail();
    }
}
