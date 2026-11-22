<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** A DNSC campus (reference data from config/dnsc_campuses.php, seeded by DnscCatalogSeeder). */
class Campus extends Model
{
    protected $table = 'campuses';

    protected $fillable = ['campus_name', 'location'];

    /** The degree programs this campus offers. */
    public function programs(): HasMany
    {
        return $this->hasMany(Program::class);
    }
}
