<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Provider Portal operations:
 *  - provider_applications   a student shares a match with the provider (explicit consent)
 *  - scholars                a student who holds one of the provider's scholarships
 *  - scholar_requirements    per-term renewal requirements, with an uploaded file (private disk)
 *  - stipend_disbursements   one stipend per scholar per term: pending / released / on hold
 *  - scholarship_revisions   who changed a scholarship, and what it looked like before/after
 * plus scholarships.renewal_requirements (the per-term checklist the provider maintains).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scholarships', function (Blueprint $table) {
            $table->json('renewal_requirements')->nullable()->after('documents');
            $table->foreignId('updated_by')->nullable()->after('verified_on')->constrained('users')->nullOnDelete();
        });

        Schema::create('provider_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('scholarship_id')->constrained()->cascadeOnDelete();
            $table->string('status', 20)->default('submitted');      // submitted | approved | rejected | withdrawn
            $table->text('snapshot')->nullable();                     // encrypted: program, campus, year level at share time
            $table->timestamp('consented_at');
            $table->timestamp('decided_at')->nullable();
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('decision_note', 500)->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'scholarship_id']);
            $table->index(['scholarship_id', 'status']);
        });

        Schema::create('scholars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('scholarship_id')->constrained()->cascadeOnDelete();
            $table->foreignId('provider_application_id')->nullable()->constrained()->nullOnDelete();
            $table->string('student_number', 30)->nullable();
            $table->string('program', 150)->nullable();
            $table->string('year_level', 20)->nullable();
            $table->string('status', 20)->default('active');          // active | probation | on_leave | graduated | terminated
            $table->string('started_term', 60)->nullable();
            $table->decimal('stipend_amount', 10, 2)->nullable();     // per term, set by the provider
            $table->timestamps();

            $table->unique(['user_id', 'scholarship_id']);
            $table->index(['scholarship_id', 'status']);
        });

        Schema::create('scholar_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scholar_id')->constrained()->cascadeOnDelete();
            $table->string('term', 60);
            $table->string('name', 200);
            $table->string('status', 20)->default('missing');         // missing | submitted | approved | needs_revision
            $table->string('file_path')->nullable();                  // private disk; never public
            $table->string('original_name', 200)->nullable();
            $table->string('mime', 100)->nullable();
            $table->unsignedInteger('size')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->string('review_note', 500)->nullable();
            $table->timestamps();

            $table->unique(['scholar_id', 'term', 'name']);
            $table->index(['term', 'status']);
        });

        Schema::create('stipend_disbursements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scholar_id')->constrained()->cascadeOnDelete();
            $table->string('term', 60);
            $table->decimal('amount', 10, 2);
            $table->string('status', 20)->default('pending');         // pending | released (on_hold removed 2026-11-22)
            $table->timestamp('released_at')->nullable();
            $table->string('reference', 100)->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['scholar_id', 'term']);
            $table->index(['term', 'status']);
        });

        Schema::create('scholarship_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scholarship_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->json('before');
            $table->json('after');
            $table->boolean('rules_changed')->default(false);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scholarship_revisions');
        Schema::dropIfExists('stipend_disbursements');
        Schema::dropIfExists('scholar_requirements');
        Schema::dropIfExists('scholars');
        Schema::dropIfExists('provider_applications');

        Schema::table('scholarships', function (Blueprint $table) {
            $table->dropConstrainedForeignId('updated_by');
            $table->dropColumn('renewal_requirements');
        });
    }
};
