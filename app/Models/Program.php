<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Program extends Model
{
    protected $fillable = ['program_name', 'campus_id'];

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }
}
