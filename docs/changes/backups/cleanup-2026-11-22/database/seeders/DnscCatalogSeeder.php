<?php

namespace Database\Seeders;

use App\Models\Campus;
use App\Models\Program;
use Illuminate\Database\Seeder;

/**
 * Makes the `campuses` and `programs` tables (the form's campus → program lists) match
 * config/dnsc_campuses.php: it adds what is missing and, since 2026-11-19, REMOVES a program that a campus no
 * longer lists, so the form only offers what each campus really has. Campuses are never deleted, and a program
 * that stays keeps its id. A student whose saved form points at a removed program is simply asked to pick again
 * (the matcher then treats the program as "confirm", never a fail).
 *
 *   php artisan db:seed --class=DnscCatalogSeeder
 */
class DnscCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $campuses = 0;
        $added = 0;
        $removed = 0;

        foreach (config('dnsc_campuses.campuses', []) as $name => $info) {
            $campus = Campus::firstOrCreate(['campus_name' => $name], ['location' => $info['location'] ?? null]);
            $campuses += $campus->wasRecentlyCreated ? 1 : 0;

            $offered = array_values(array_unique($info['programs'] ?? []));
            foreach ($offered as $program) {
                $row = Program::firstOrCreate(['campus_id' => $campus->id, 'program_name' => $program]);
                $added += $row->wasRecentlyCreated ? 1 : 0;
            }
            $removed += Program::where('campus_id', $campus->id)->whereNotIn('program_name', $offered)->delete();
        }

        $this->command?->info("DNSC catalog ready: {$campuses} new campuses, {$added} programs added, {$removed} removed.");
    }
}
