<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Links a `provider` role account to one entry in config/providers.php.
// Set server-side only (ProviderAccountSeeder); never from request input.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('provider_key', 30)->nullable()->index()->after('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['provider_key']);
            $table->dropColumn('provider_key');
        });
    }
};
