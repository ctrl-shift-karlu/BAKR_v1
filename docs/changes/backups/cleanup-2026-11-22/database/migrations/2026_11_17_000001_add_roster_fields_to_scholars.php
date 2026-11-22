<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 * Official scholar roster: a provider can add its existing scholars (one by one or from an
 * Excel/CSV file) even when they have no BAKR account yet.
 *  - user_id becomes nullable: roster scholars have no account
 *  - full_name / email: who the scholar is when there is no account (an account's own name wins)
 *  - added_by: the provider user who added the row (null = created by approving an applicant)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scholars', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();
            $table->string('full_name', 150)->nullable()->after('user_id');
            $table->string('email', 190)->nullable()->after('full_name');
            $table->foreignId('added_by')->nullable()->after('stipend_amount')->constrained('users')->nullOnDelete();
            $table->index(['scholarship_id', 'student_number']);
        });
    }

    public function down(): void
    {
        // Roster rows have no account and cannot survive user_id becoming required again.
        DB::table('scholars')->whereNull('user_id')->delete();

        Schema::table('scholars', function (Blueprint $table) {
            $table->dropIndex(['scholarship_id', 'student_number']);
            $table->dropConstrainedForeignId('added_by');
            $table->dropColumn(['full_name', 'email']);
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
        });
    }
};
