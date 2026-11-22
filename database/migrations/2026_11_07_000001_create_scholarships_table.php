<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// If your EERD already has a scholarships table, compare it with this one first.
// The rules column holds the matching rules (see config/scholarships.php) so an admin
// screen can edit them later without a code change.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scholarships', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 60)->unique();
            $table->string('name');
            $table->string('sector', 20);                 // internal | government | private
            $table->string('provider')->nullable();
            $table->string('official_url')->nullable();
            $table->text('overview');
            $table->json('benefits');
            $table->json('documents');                    // information only: nothing is uploaded to BAKR
            $table->text('application_route')->nullable();
            $table->string('window_note')->nullable();
            $table->text('source_note')->nullable();
            $table->json('rules');
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->date('verified_on')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scholarships');
    }
};
