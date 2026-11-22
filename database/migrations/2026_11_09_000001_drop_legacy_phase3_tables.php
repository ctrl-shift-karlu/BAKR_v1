<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Drops the unused Phase 3 document-upload tables.
 *
 * The student flow now stores answers in scholarship_applications; these
 * tables (and their models/services) are no longer read or written.
 * down() recreates the empty schema; it does not restore data, so back up
 * the database before running this on a populated install.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Children before parents because of foreign keys.
        Schema::dropIfExists('application_documents');
        Schema::dropIfExists('student_applications');
        Schema::dropIfExists('student_profiles');
        Schema::dropIfExists('document_types');
    }

    public function down(): void
    {
        Schema::create('document_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('student_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('first_name', 100);
            $table->string('middle_name', 100)->nullable();
            $table->string('last_name', 100);
            $table->date('birth_date');
            $table->string('contact_number', 20)->nullable();
            $table->string('municipality', 100)->nullable();
            $table->string('province', 100)->nullable();
            $table->timestamps();
        });

        Schema::create('student_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_profile_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('version');
            $table->boolean('is_current')->default(true);
            $table->string('status', 30)->default('submitted');
            $table->string('educational_level', 20);
            $table->foreignId('campus_id')->constrained()->restrictOnDelete();
            $table->foreignId('program_id')->constrained()->restrictOnDelete();
            $table->unsignedTinyInteger('year_level');
            $table->unsignedSmallInteger('graduation_year');
            $table->decimal('gwa', 5, 2)->nullable();
            $table->string('gwa_scale', 10)->nullable();
            $table->string('academic_standing', 100)->nullable();
            $table->string('honors', 150)->nullable();
            $table->string('income_bracket', 20)->nullable();
            $table->unsignedTinyInteger('dependents')->nullable();
            $table->text('other_qualifications')->nullable();
            $table->timestamp('submitted_at')->useCurrent();
            $table->timestamps();

            $table->unique(['student_profile_id', 'version']);
            $table->index(['student_profile_id', 'is_current']);
            $table->index('status');
        });

        Schema::create('application_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_application_id')->constrained()->cascadeOnDelete();
            $table->foreignId('document_type_id')->constrained()->restrictOnDelete();
            $table->string('original_name');
            $table->string('file_path');
            $table->string('mime_type', 100);
            $table->unsignedInteger('size_bytes');
            $table->string('status', 30)->default('pending');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['student_application_id', 'document_type_id'], 'app_docs_application_type_unique');
        });
    }
};
