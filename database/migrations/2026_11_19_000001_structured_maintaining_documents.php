<?php

use App\Support\MaintainingDocument;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Maintaining documents get their own settings (owner, 2026-11-19): instructions, accepted file types,
 * size limit, and whether every scholar must submit it. scholarships.renewal_requirements becomes a list
 * of {name, note, types, max_mb, required}; each scholar's checklist item keeps a copy of those settings
 * (so uploads, Inactive and stipend release read the item itself).
 * Existing plain-text items are converted with MaintainingDocument::fromText(): only their own wording
 * is used ("If …" → only when it applies, "PDF copy" → PDF only).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scholar_requirements', function (Blueprint $table) {
            $table->boolean('is_optional')->default(false)->after('name');
            $table->json('accepted_types')->nullable()->after('is_optional');
            $table->unsignedInteger('max_kb')->nullable()->after('accepted_types');
            $table->string('instructions', 300)->nullable()->after('max_kb');
        });

        foreach (DB::table('scholarships')->get(['id', 'renewal_requirements']) as $s) {
            $docs = MaintainingDocument::list(json_decode((string) $s->renewal_requirements, true) ?: []);
            DB::table('scholarships')->where('id', $s->id)->update(['renewal_requirements' => json_encode($docs)]);

            $scholarIds = DB::table('scholars')->where('scholarship_id', $s->id)->pluck('id');
            foreach ($docs as $d) {
                DB::table('scholar_requirements')->whereIn('scholar_id', $scholarIds)->where('name', $d['name'])->update([
                    'is_optional' => ! $d['required'],
                    'accepted_types' => json_encode($d['types']),
                    'max_kb' => $d['max_mb'] * 1024,
                    'instructions' => $d['note'],
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('scholar_requirements', function (Blueprint $table) {
            $table->dropColumn(['is_optional', 'accepted_types', 'max_kb', 'instructions']);
        });

        // Back to plain names.
        foreach (DB::table('scholarships')->get(['id', 'renewal_requirements']) as $s) {
            $names = array_map(fn ($d) => is_array($d) ? ($d['name'] ?? '') : $d, json_decode((string) $s->renewal_requirements, true) ?: []);
            DB::table('scholarships')->where('id', $s->id)->update(['renewal_requirements' => json_encode(array_values(array_filter($names)))]);
        }
    }
};
