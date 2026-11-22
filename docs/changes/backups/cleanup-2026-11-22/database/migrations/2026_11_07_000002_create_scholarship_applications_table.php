<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// One row per saved version of a student's answers. Answers are encrypted at rest
// (income range, special groups and similar details are sensitive under the Data Privacy Act).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scholarship_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('version');
            $table->boolean('is_current')->default(true);
            $table->string('status', 20)->default('submitted');   // submitted | updated
            $table->longText('answers');                           // encrypted JSON
            $table->text('ai_summary')->nullable();
            $table->timestamp('ai_summary_at')->nullable();
            $table->timestamp('submitted_at')->useCurrent();
            $table->timestamps();

            $table->unique(['user_id', 'version'], 'sch_apps_user_version_unique');
            $table->index(['user_id', 'is_current'], 'sch_apps_user_current_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scholarship_applications');
    }
};
