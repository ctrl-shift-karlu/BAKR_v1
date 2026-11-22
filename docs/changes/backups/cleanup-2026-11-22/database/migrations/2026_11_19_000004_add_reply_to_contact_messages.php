<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** The admin answers a message from inside BAKR (e-mailed to the sender); the last reply is kept on the message. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contact_messages', function (Blueprint $table) {
            if (! Schema::hasColumn('contact_messages', 'reply')) {
                $table->text('reply')->nullable()->after('message');
            }
            if (! Schema::hasColumn('contact_messages', 'replied_at')) {
                $table->timestamp('replied_at')->nullable()->after('emailed_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('contact_messages', function (Blueprint $table) {
            $table->dropColumn(['reply', 'replied_at']);
        });
    }
};
