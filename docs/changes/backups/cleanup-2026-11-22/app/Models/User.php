<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password'];

    protected $hidden = ['password', 'remember_token'];

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

    /** Scholarships held by a scholar account (admin Accounts list). */
    public function scholars(): HasMany
    {
        return $this->hasMany(Scholar::class);
    }
}
