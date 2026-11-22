<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Gemini AI matcher (owner, 2026-11-19). Hybrid: the rule engine still checks the published hard
 * limits (rule_status); Gemini reviews the whole de-identified application (ai_status, ai_fit,
 * ai_notes). The shown `status` is the stricter of the two.
 *
 * scholarship_applications.ai_match_status: pending (rule results only, AI not run yet) | done |
 * failed (AI unreachable / bad reply; rule results stay) | off (no Gemini key on this server).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scholarship_matches', function (Blueprint $table) {
            $table->string('rule_status', 20)->nullable()->after('status');
            $table->string('ai_status', 20)->nullable()->after('rule_status');
            $table->string('ai_fit', 10)->nullable()->after('ai_status');
            $table->json('ai_notes')->nullable()->after('ai_fit');
        });

        Schema::table('scholarship_applications', function (Blueprint $table) {
            $table->string('ai_match_status', 20)->nullable()->after('ai_summary_at');
            $table->timestamp('ai_matched_at')->nullable()->after('ai_match_status');
            $table->string('ai_model', 80)->nullable()->after('ai_matched_at');
        });
    }

    public function down(): void
    {
        Schema::table('scholarship_matches', function (Blueprint $table) {
            $table->dropColumn(['rule_status', 'ai_status', 'ai_fit', 'ai_notes']);
        });
        Schema::table('scholarship_applications', function (Blueprint $table) {
            $table->dropColumn(['ai_match_status', 'ai_matched_at', 'ai_model']);
        });
    }
};
