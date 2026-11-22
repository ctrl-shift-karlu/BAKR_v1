<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->string('topic', 30);
            $table->string('name', 80);
            $table->string('email', 120);
            $table->unsignedTinyInteger('rating')->nullable();   // feedback only, 1-5
            $table->text('message');
            $table->boolean('share_publicly')->default(false);    // the sender agreed to be quoted on the site
            $table->timestamp('approved_at')->nullable();         // set by staff before anything is shown publicly
            $table->timestamp('emailed_at')->nullable();          // null = the notification email did not go out
            $table->timestamp('handled_at')->nullable();          // staff marks it answered
            $table->timestamps();

            $table->index(['topic', 'share_publicly', 'approved_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_messages');
    }
};
