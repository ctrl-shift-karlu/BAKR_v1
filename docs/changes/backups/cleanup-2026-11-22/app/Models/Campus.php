<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campus extends Model
{
    protected $table = 'campuses';

    protected $fillable = ['campus_name', 'location'];

    public function programs(): HasMany
    {
        return $this->hasMany(Program::class);
    }
}
