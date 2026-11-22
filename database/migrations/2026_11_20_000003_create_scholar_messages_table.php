<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Correction requests from a scholar to the provider of ONE of their scholarships (owner, 2026-11-20):
 * the scholar's details are kept by the provider, so "Request a correction" goes to that provider's
 * Messages page, never to the admin. The provider's reply is kept here and e-mailed to the scholar.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scholar_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scholar_id')->constrained('scholars')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();   // the scholar account that wrote it
            $table->string('topic', 30);
            $table->text('message');
            $table->text('reply')->nullable();
            $table->foreignId('replied_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('replied_at')->nullable();
            $table->timestamp('reply_emailed_at')->nullable();
            $table->timestamp('handled_at')->nullable();
            $table->timestamps();

            $table->index(['scholar_id', 'handled_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scholar_messages');
    }
};
