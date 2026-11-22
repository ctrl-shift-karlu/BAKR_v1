<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * A message to the BAKR team: from the public Contact page, the About feedback form or a portal Help page.
 * The admin reads, answers and marks them handled; shared feedback appears publicly only after approval.
 */
class ContactMessage extends Model
{
    public const TOPICS = [
        'question' => 'Question about BAKR',
        'feedback' => 'Feedback or suggestion',
        'problem' => 'Something is not working',
        'info' => 'Scholarship info needs updating',
    ];

    /** Star rating words (feedback only). */
    public const RATINGS = [
        1 => 'Poor',
        2 => 'Needs improvement',
        3 => 'Good',
        4 => 'Very good',
        5 => 'Excellent',
    ];

    /** Parts of BAKR a feedback sender can say they used (About page feedback, `aspects`). */
    public const ASPECTS = [
        'matching' => 'Scholarship matching',
        'form' => 'The matching form',
        'catalogue' => 'Scholarship information',
        'monitoring' => 'Scholar monitoring',
        'ease' => 'Easy to use',
        'mobile' => 'Works well on my phone',
    ];

    protected $fillable = ['topic', 'name', 'email', 'rating', 'aspects', 'message', 'reply', 'replied_at', 'share_publicly', 'approved_at', 'emailed_at', 'handled_at'];

    protected $casts = [
        'aspects' => 'array',
        'share_publicly' => 'boolean',
        'approved_at' => 'datetime',
        'emailed_at' => 'datetime',
        'replied_at' => 'datetime',
        'handled_at' => 'datetime',
    ];

    /** The topic in words, e.g. "Question about BAKR". */
    public function topicLabel(): string
    {
        return self::TOPICS[$this->topic] ?? ucfirst($this->topic);
    }

    /** @return list<string> labels of the parts of BAKR the sender used */
    public function aspectLabels(): array
    {
        return array_values(array_intersect_key(self::ASPECTS, array_flip((array) $this->aspects)));
    }

    /** Feedback the sender agreed to share AND staff approved. Nothing else is ever shown publicly. */
    public function scopePublicFeedback(Builder $query): Builder
    {
        return $query->where('topic', 'feedback')->where('share_publicly', true)->whereNotNull('approved_at');
    }
}
