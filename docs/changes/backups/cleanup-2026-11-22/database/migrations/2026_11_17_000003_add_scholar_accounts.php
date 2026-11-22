<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Adding a scholar creates their BAKR student account (ScholarAccounts):
 *  - users.must_change_password    true while the account still has the e-mailed temporary password;
 *                                  the student area sends them to "Change your password" until it is changed.
 *  - scholars.credentials_sent_at  when the login e-mail went out (null = not sent / failed: the provider can resend).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('must_change_password')->default(false)->after('status');
        });
        Schema::table('scholars', function (Blueprint $table) {
            $table->timestamp('credentials_sent_at')->nullable()->after('added_by');
        });
    }

    public function down(): void
    {
        Schema::table('scholars', fn (Blueprint $table) => $table->dropColumn('credentials_sent_at'));
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('must_change_password'));
    }
};
