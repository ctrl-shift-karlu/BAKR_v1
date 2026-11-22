<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * About page feedback (2026-11-21): a star rating plus the parts of BAKR the person used
 * (keys of ContactMessage::ASPECTS). Feedback only; null for every other message.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contact_messages', function (Blueprint $table) {
            $table->json('aspects')->nullable()->after('rating');
        });
    }

    public function down(): void
    {
        Schema::table('contact_messages', function (Blueprint $table) {
            $table->dropColumn('aspects');
        });
    }
};
