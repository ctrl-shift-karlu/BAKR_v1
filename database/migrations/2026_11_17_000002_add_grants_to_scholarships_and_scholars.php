<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 * Published stipend per scholarship, so adding a scholar fills it in automatically.
 *  - scholarships.grants   JSON list of {key, label, amount, per: semester|month} from config/scholarships.php.
 *                          Empty when the provider publishes no fixed amount (DNSC, Cebuana "up to").
 *                          More than one entry = the scholar has a type, e.g. CMSP Full / Half.
 *  - scholars.grant_type   the chosen entry's key (null when the scholarship has no grants).
 * Existing scholarship rows are filled from config by slug here, so no re-seed is needed.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scholarships', function (Blueprint $table) {
            $table->json('grants')->nullable()->after('benefits');
        });
        Schema::table('scholars', function (Blueprint $table) {
            $table->string('grant_type', 30)->nullable()->after('status');
        });

        foreach (config('scholarships.scholarships', []) as $s) {
            if (! empty($s['grants'])) {
                DB::table('scholarships')->where('slug', $s['slug'])->update(['grants' => json_encode($s['grants'])]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('scholars', fn (Blueprint $table) => $table->dropColumn('grant_type'));
        Schema::table('scholarships', fn (Blueprint $table) => $table->dropColumn('grants'));
    }
};
