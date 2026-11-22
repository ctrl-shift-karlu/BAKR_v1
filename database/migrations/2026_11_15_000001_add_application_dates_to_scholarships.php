<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Real dates for each scholarship's current call, edited by the provider in "My scholarships".
 * All optional: when a provider has not published a date, BAKR shows no open/closed status.
 * The free-text window_note stays for extra details (e.g. "results by the last week of October").
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scholarships', function (Blueprint $table) {
            $table->date('application_opens_on')->nullable()->after('window_note');
            $table->date('application_closes_on')->nullable()->after('application_opens_on');
            $table->date('results_on')->nullable()->after('application_closes_on');
        });
    }

    public function down(): void
    {
        Schema::table('scholarships', function (Blueprint $table) {
            $table->dropColumn(['application_opens_on', 'application_closes_on', 'results_on']);
        });
    }
};
