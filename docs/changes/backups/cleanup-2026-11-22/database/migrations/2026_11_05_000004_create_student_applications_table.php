<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_profile_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('version');
            $table->boolean('is_current')->default(true);
            $table->string('status', 30)->default('submitted');

            // Educational information
            $table->string('educational_level', 20);
            $table->foreignId('campus_id')->constrained()->restrictOnDelete();
            $table->foreignId('program_id')->constrained()->restrictOnDelete();
            $table->unsignedTinyInteger('year_level');
            $table->unsignedSmallInteger('graduation_year');

            // Academic information (structured so it can be filtered and matched)
            $table->decimal('gwa', 5, 2)->nullable();
            $table->string('gwa_scale', 10)->nullable();
            $table->string('academic_standing', 100)->nullable();
            $table->string('honors', 150)->nullable();

            // Family and financial information (optional / sensitive)
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

        Schema::create('student_consents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('consent_type', 50);
            $table->timestamp('accepted_at');
            $table->timestamps();

            $table->unique(['user_id', 'consent_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_consents');
        Schema::dropIfExists('application_documents');
        Schema::dropIfExists('student_applications');
    }
};
