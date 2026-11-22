<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Owner, 2026-11-20 (later the same day):
 *  - the Scholar ID is simply typed by the provider: no per-scholarship format any more, so
 *    scholarships.scholar_id_format is dropped (saved Scholar IDs stay exactly as they are);
 *  - gender is Male or Female only: any other value saved earlier ("other", "undisclosed") is cleared,
 *    so the provider is asked for it the next time they edit that scholar's personal information.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('scholarships', 'scholar_id_format')) {
            Schema::table('scholarships', function (Blueprint $table) {
                $table->dropColumn('scholar_id_format');
            });
        }

        if (Schema::hasColumn('scholars', 'gender')) {
            DB::table('scholars')->whereNotNull('gender')->whereNotIn('gender', ['male', 'female'])->update(['gender' => null]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('scholarships', 'scholar_id_format')) {
            Schema::table('scholarships', function (Blueprint $table) {
                $table->string('scholar_id_format', 30)->default('SCID-0000-0000')->after('results_on');
            });
        }
    }
};
