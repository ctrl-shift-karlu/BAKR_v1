<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/*
 * Scholar accounts are separate from student accounts (owner's decision 2026-11-18).
 * Until now a provider-added scholar was a STUDENT account that held a `scholars` row, so the
 * Student login opened the scholar portal. This moves every such account to role "scholar":
 * they now sign in at /scholarLogin. Passwords, e-mails and all records stay as they are.
 *
 * down(): turns those scholar accounts back into student accounts.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->where('role', 'student')
            ->whereIn('id', DB::table('scholars')->whereNotNull('user_id')->select('user_id'))
            ->update(['role' => 'scholar']);
    }

    public function down(): void
    {
        DB::table('users')
            ->where('role', 'scholar')
            ->whereIn('id', DB::table('scholars')->whereNotNull('user_id')->select('user_id'))
            ->update(['role' => 'student']);
    }
};
