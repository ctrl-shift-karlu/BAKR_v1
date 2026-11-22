<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Stipends can be released per semester (one record per scholar per term, as before) or
 * per month (one record per scholar per month of the term).
 *  - release_type  semester | monthly (existing rows become "semester")
 *  - period_month  "YYYY-MM" for monthly rows, "" for semester rows (not NULL, so the
 *                  unique key still allows only one semester record per scholar per term)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stipend_disbursements', function (Blueprint $table) {
            $table->string('release_type', 10)->default('semester')->after('term');
            $table->string('period_month', 7)->default('')->after('release_type');
        });

        // Add the new unique key first: MySQL needs an index starting with scholar_id for the
        // foreign key, so the old (scholar_id, term) key can only be dropped after this exists.
        Schema::table('stipend_disbursements', function (Blueprint $table) {
            $table->unique(['scholar_id', 'term', 'period_month'], 'stipend_disbursements_scholar_term_period_unique');
        });
        Schema::table('stipend_disbursements', function (Blueprint $table) {
            $table->dropUnique(['scholar_id', 'term']);
        });
    }

    public function down(): void
    {
        // Monthly rows cannot fit the old one-per-term key, so rolling back deletes them.
        DB::table('stipend_disbursements')->where('release_type', 'monthly')->delete();
        Schema::table('stipend_disbursements', function (Blueprint $table) {
            $table->unique(['scholar_id', 'term']);
        });
        Schema::table('stipend_disbursements', function (Blueprint $table) {
            $table->dropUnique('stipend_disbursements_scholar_term_period_unique');
            $table->dropColumn(['release_type', 'period_month']);
        });
    }
};
