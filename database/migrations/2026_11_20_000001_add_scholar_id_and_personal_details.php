<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Owner, 2026-11-20:
 *  - every scholar gets a Scholar ID for their scholarship (scholars.scholar_code), which the provider
 *    searches and filters by. Its format is set per scholarship on My scholarships
 *    (scholarships.scholar_id_format, "0" = one digit; default SCID-0000-0000, App\Support\ScholarIdFormat).
 *    One Scholar ID per scholarship: unique (scholarship_id, scholar_code); older rows stay empty.
 *  - personal details the provider records: birthday + gender (personal information) and
 *    mobile number + address (contact information). All optional, so older rows stay valid.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('scholarships', 'scholar_id_format')) {
            Schema::table('scholarships', function (Blueprint $table) {
                $table->string('scholar_id_format', 30)->default('SCID-0000-0000')->after('results_on');
            });
        }

        if (! Schema::hasColumn('scholars', 'scholar_code')) {
            Schema::table('scholars', function (Blueprint $table) {
                $table->string('scholar_code', 40)->nullable()->after('scholarship_id');
                $table->date('birthdate')->nullable()->after('email');
                $table->string('gender', 20)->nullable()->after('birthdate');
                $table->string('mobile', 20)->nullable()->after('gender');
                $table->string('address', 255)->nullable()->after('mobile');
                $table->unique(['scholarship_id', 'scholar_code'], 'scholars_scholarship_code_unique');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('scholars', 'scholar_code')) {
            Schema::table('scholars', function (Blueprint $table) {
                $table->dropUnique('scholars_scholarship_code_unique');
                $table->dropColumn(['scholar_code', 'birthdate', 'gender', 'mobile', 'address']);
            });
        }
        if (Schema::hasColumn('scholarships', 'scholar_id_format')) {
            Schema::table('scholarships', function (Blueprint $table) {
                $table->dropColumn('scholar_id_format');
            });
        }
    }
};
