<?php

namespace App\Models;

use App\Support\PersonName;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

/**
 * Every BAKR login. The role decides the portal: student (matching), scholar (monitoring, created by a provider),
 * provider (provider_key = its entry in config/providers.php) or admin. Role and status are only set on the server.
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password'];

    protected $hidden = ['password', 'remember_token'];

    /** Dates as Carbon objects; the password is hashed automatically when it is set. */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'must_change_password' => 'boolean',
        ];
    }

    /**
     * Signs this account out everywhere except the session $keep (security audit, 2026-11-22): after a
     * password change or reset, and when the admin deactivates the account. Sessions live in the database
     * (SESSION_DRIVER=database); with another driver this does nothing (the remember_token change still
     * ends "Keep me signed in" cookies). Returns how many sessions were ended.
     */
    public function endSessions(?string $keep = null): int
    {
        if (config('session.driver') !== 'database') {
            return 0;
        }

        return DB::connection(config('session.connection'))->table(config('session.table', 'sessions'))
            ->where('user_id', $this->getKey())
            ->when($keep, fn ($q) => $q->where('id', '!=', $keep))
            ->delete();
    }

    /**
     * Sets the name parts (owner, 2026-11-23: first name, middle name, surname, suffix) and the one-line `name`
     * composed from them. Expects PersonName::parts() output (or any array with those keys). Does not save.
     */
    public function fillName(array $parts): static
    {
        $p = PersonName::parts($parts);

        return $this->forceFill(Arr::only($p, PersonName::FIELDS) + ['name' => $p['full']]);
    }

    /** The first name for greetings ("Hello, Juan"): the stored part, else the first word of the one-line name. */
    public function firstName(): string
    {
        return $this->first_name ?: (explode(' ', trim((string) $this->name))[0] ?? '');
    }

    /** Two initials for avatars: first name + surname when known, else the first two words of the name. */
    public function initials(): string
    {
        $words = $this->first_name && $this->last_name ? [$this->first_name, $this->last_name] : preg_split('/\s+/u', trim((string) $this->name));

        return collect($words)->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');
    }

    /** Scholarships held by a scholar account (admin Accounts list). */
    public function scholars(): HasMany
    {
        return $this->hasMany(Scholar::class);
    }
}
