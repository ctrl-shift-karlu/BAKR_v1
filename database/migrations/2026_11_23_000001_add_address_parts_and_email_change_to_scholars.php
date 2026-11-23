<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Owner, 2026-11-23:
 *  - the scholar's address is recorded in parts: street (house no., street, purok / barangay), city or
 *    municipality, province. `address` stays and holds the three joined ("Purok 3, Gredu, Panabo City,
 *    Davao del Norte") so older rows, which only have the one line, still read the same.
 *  - the provider can change a scholar's sign-in e-mail. The new address is kept as `pending_email` until the
 *    scholar opens the confirmation link sent to it (`pending_email_token` = sha256 of the link's token), so an
 *    address that does not work never replaces one that does.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scholars', function (Blueprint $table) {
            if (! Schema::hasColumn('scholars', 'address_street')) {
                $table->string('address_street', 150)->nullable()->after('address');
                $table->string('address_city', 80)->nullable()->after('address_street');
                $table->string('address_province', 80)->nullable()->after('address_city');
            }
            if (! Schema::hasColumn('scholars', 'pending_email')) {
                $table->string('pending_email', 190)->nullable()->after('email');
                $table->string('pending_email_token', 64)->nullable()->after('pending_email');
                $table->timestamp('pending_email_sent_at')->nullable()->after('pending_email_token');
            }
        });
    }

    public function down(): void
    {
        Schema::table('scholars', function (Blueprint $table) {
            foreach (['address_street', 'address_city', 'address_province', 'pending_email', 'pending_email_token', 'pending_email_sent_at'] as $column) {
                if (Schema::hasColumn('scholars', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
