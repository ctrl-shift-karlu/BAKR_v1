<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Scholars get a campus (owner, 2026-11-19), so the admin's analytics can show scholars per campus.
 * Scholars already on the roster are filled in where their program is offered at exactly one DNSC
 * campus (config/dnsc_campuses.php); the others stay empty until their provider picks one.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('scholars', 'campus')) {
            Schema::table('scholars', function (Blueprint $table) {
                $table->string('campus', 80)->nullable()->after('program');
            });
        }

        $campusesOf = [];
        foreach ((array) config('dnsc_campuses.campuses', []) as $campus => $c) {
            foreach ($c['programs'] ?? [] as $program) {
                $campusesOf[$program][] = $campus;
            }
        }
        foreach ($campusesOf as $program => $campuses) {
            if (count($campuses) === 1) {
                DB::table('scholars')->whereNull('campus')->where('program', $program)->update(['campus' => $campuses[0]]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('scholars', 'campus')) {
            Schema::table('scholars', function (Blueprint $table) {
                $table->dropColumn('campus');
            });
        }
    }
};
