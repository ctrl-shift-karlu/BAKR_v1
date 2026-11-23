<?php

use App\Support\PersonName;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Owner, 2026-11-23: names in parts. First name + surname required, middle name + suffix optional, for student and
 * scholar accounts (users) and scholars (scholars). The one-line name (`users.name`, `scholars.full_name`) stays and is
 * composed from the parts, so every page that shows it keeps working.
 *
 * Existing rows are split once with PersonName::split() (surname = last word plus particles such as "Dela"; a trailing
 * Jr. / III becomes the suffix; the middle name is left empty). The one-line name itself is not changed. Providers and
 * students can correct the parts on the forms. Provider and admin accounts are not people: left as they are.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['users' => 'name', 'scholars' => 'full_name'] as $table => $after) {
            if (! Schema::hasColumn($table, 'first_name')) {
                Schema::table($table, function (Blueprint $t) use ($after) {
                    $t->string('first_name', 60)->nullable()->after($after);
                    $t->string('middle_name', 60)->nullable()->after('first_name');
                    $t->string('last_name', 60)->nullable()->after('middle_name');
                    $t->string('suffix', 10)->nullable()->after('last_name');
                });
            }
        }

        DB::table('users')->whereIn('role', ['student', 'scholar'])->whereNull('first_name')->orderBy('id')
            ->each(function ($u) {
                DB::table('users')->where('id', $u->id)->update(self::cut(PersonName::split($u->name)));
            });

        DB::table('scholars')->leftJoin('users', 'users.id', '=', 'scholars.user_id')->whereNull('scholars.first_name')
            ->orderBy('scholars.id')->select('scholars.id', 'scholars.full_name', 'users.name as account_name')
            ->each(function ($s) {
                $name = $s->account_name ?: $s->full_name;   // the account name is what the pages show first
                DB::table('scholars')->where('id', $s->id)->update(self::cut(PersonName::split($name)));
            });
    }

    /** Keeps each part inside its column (very long old names are cut, never rejected). */
    private static function cut(array $parts): array
    {
        return array_map(fn ($v) => $v === null ? null : mb_substr($v, 0, 60), $parts);
    }

    public function down(): void
    {
        foreach (['users', 'scholars'] as $table) {
            Schema::table($table, function (Blueprint $t) use ($table) {
                foreach (['first_name', 'middle_name', 'last_name', 'suffix'] as $c) {
                    if (Schema::hasColumn($table, $c)) {
                        $t->dropColumn($c);
                    }
                }
            });
        }
    }
};
