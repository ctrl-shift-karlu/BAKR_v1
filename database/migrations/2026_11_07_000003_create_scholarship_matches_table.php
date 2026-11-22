<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scholarship_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scholarship_application_id')->constrained()->cascadeOnDelete();
            $table->foreignId('scholarship_id')->constrained()->cascadeOnDelete();
            $table->string('status', 20);                 // eligible | possible | needs_info | not_eligible
            $table->unsignedTinyInteger('score');         // closeness, 0-100
            $table->json('checks');                       // per-rule result and plain-language detail
            $table->text('explanation')->nullable();      // AI explanation, cached
            $table->string('explanation_source', 30)->nullable();   // claude | template
            $table->timestamp('explained_at')->nullable();
            $table->timestamps();

            $table->unique(['scholarship_application_id', 'scholarship_id'], 'sch_matches_app_sch_unique');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scholarship_matches');
    }
};
