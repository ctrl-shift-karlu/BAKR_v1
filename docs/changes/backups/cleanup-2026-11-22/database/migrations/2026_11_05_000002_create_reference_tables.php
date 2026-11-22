<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Shared reference tables from the guide (6.1 / 6.6). Each is guarded so this
// migration is a no-op for tables your existing EERD already provides.
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('campuses')) {
            Schema::create('campuses', function (Blueprint $table) {
                $table->id();
                $table->string('campus_name')->unique();
                $table->string('location')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('programs')) {
            Schema::create('programs', function (Blueprint $table) {
                $table->id();
                $table->string('program_name');
                $table->foreignId('campus_id')->constrained()->restrictOnDelete();
                $table->timestamps();

                $table->unique(['campus_id', 'program_name']);
            });
        }

        if (! Schema::hasTable('document_types')) {
            Schema::create('document_types', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('description')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('document_types');
        Schema::dropIfExists('programs');
        Schema::dropIfExists('campuses');
    }
};
