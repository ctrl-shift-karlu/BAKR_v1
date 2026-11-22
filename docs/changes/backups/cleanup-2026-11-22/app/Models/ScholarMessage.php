<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A scholar's correction request to the provider of one of their scholarships (owner, 2026-11-20).
 * Sent from the scholar's Profile ("Request a correction"), received on the provider's Messages page
 * (ProviderScope::messages()), never by the admin. The provider's last reply is kept on the row.
 */
class ScholarMessage extends Model
{
    /** What needs correcting: the same groups as the scholar's Profile cards (and the provider's profile cards). */
    public const TOPICS = [
        'personal' => 'Personal information (name, birthday, gender)',
        'contact' => 'Contact information (e-mail, mobile number, address)',
        'school' => 'School information (student number, campus, program, year level)',
        'scholarship' => 'Scholarship information (Scholar ID, grant type, status)',
        'other' => 'Something else',
    ];

    protected $fillable = ['scholar_id', 'user_id', 'topic', 'message'];

    protected function casts(): array
    {
        return [
            'replied_at' => 'datetime',
            'reply_emailed_at' => 'datetime',
            'handled_at' => 'datetime',
        ];
    }

    public function scholar(): BelongsTo
    {
        return $this->belongsTo(Scholar::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function topicLabel(): string
    {
        return self::TOPICS[$this->topic] ?? ucfirst($this->topic);
    }

    /** Short label for pills and subjects: the part before the brackets. */
    public function topicShort(): string
    {
        return trim(strtok($this->topicLabel(), '('));
    }
}
