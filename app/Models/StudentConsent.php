<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A student's agreement to the Privacy Policy (Data Privacy Act of 2012), renewed on every submit of the matching form. */
class StudentConsent extends Model
{
    /**
     * Current data privacy consent. Since 2026-11-19 it also covers sending the de-identified
     * application to the Gemini AI matcher, so students who only gave the old 'data_privacy'
     * consent are asked again on their next submit.
     */
    public const TYPE_DATA_PRIVACY = 'data_privacy_ai';

    /** Consent given before AI matching existed (kept as a record, no longer enough to submit). */
    public const TYPE_DATA_PRIVACY_LEGACY = 'data_privacy';

    protected $fillable = ['user_id', 'consent_type', 'accepted_at'];

    /** The acceptance date as a Carbon object. */
    protected function casts(): array
    {
        return ['accepted_at' => 'datetime'];
    }
}
