<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * "On hold" is gone (owner, 2026-11-22): a stipend is either not yet released or released. Held rows go back
 * to "not yet released", so the Release button (and its lock) applies to them again.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('stipend_disbursements')->where('status', 'on_hold')
            ->update(['status' => 'pending', 'released_at' => null, 'updated_at' => now()]);
    }

    public function down(): void
    {
        // Nothing to restore: which rows were on hold is not kept.
    }
};
