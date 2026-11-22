<?php

use App\Support\AcademicTerm;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Owner's decisions, 2026-11-18:
 *  - A scholar is only Active, Inactive or Terminated. "Inactive" is worked out from overdue maintaining
 *    documents, so the column keeps active | terminated: probation / on leave → active, graduated → terminated
 *    (the scholarship has ended for them).
 *  - Each requirement gets a due date (default: the last day of its semester), so "past due" can be told.
 *  - CoScho is a CHED scholarship and now sits in the CHED provider account (config/providers.php): the
 *    separate "coscho" provider login is deactivated (kept, not deleted, so nothing that points to it breaks).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scholar_requirements', function (Blueprint $table) {
            $table->date('due_on')->nullable()->after('status');
        });

        // Backfill: every existing item is due at the end of its semester.
        DB::table('scholar_requirements')->select('term')->distinct()->pluck('term')->each(function ($term) {
            if ($end = AcademicTerm::endDate((string) $term)) {
                DB::table('scholar_requirements')->where('term', $term)->update(['due_on' => $end->toDateString()]);
            }
        });

        DB::table('scholars')->whereIn('status', ['probation', 'on_leave'])->update(['status' => 'active']);
        DB::table('scholars')->where('status', 'graduated')->update(['status' => 'terminated']);

        DB::table('users')->where('role', 'provider')->where('provider_key', 'coscho')->update(['status' => 'inactive']);
    }

    public function down(): void
    {
        Schema::table('scholar_requirements', function (Blueprint $table) {
            $table->dropColumn('due_on');
        });
        // Statuses are not restored: the old probation / on leave / graduated values are not kept.
    }
};
