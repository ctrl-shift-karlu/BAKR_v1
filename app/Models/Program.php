<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A degree program offered at one DNSC campus (reference data for the matching form and the scholar roster). */
class Program extends Model
{
    protected $fillable = ['program_name', 'campus_id'];

    /** The campus that offers this program. */
    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }
}
