<?php

namespace Database\Seeders;

use App\Models\Scholarship;
use Illuminate\Database\Seeder;

/** Loads config/scholarships.php into the table. Safe to re-run: it updates by slug. */
class ScholarshipSeeder extends Seeder
{
    public function run(): void
    {
        $config = config('scholarships');

        foreach ($config['scholarships'] as $i => $s) {
            // Renewal requirements are usually set by the provider in the portal: only write them when config has them.
            // The same for application dates: they belong to the provider once set, so only the ones config publishes are written.
            $extra = array_intersect_key($s, array_flip(['renewal_requirements', 'application_opens_on', 'application_closes_on', 'results_on']));

            Scholarship::updateOrCreate(['slug' => $s['slug']], $extra + [
                'name' => $s['name'],
                'sector' => $s['sector'],
                'provider' => $s['provider'] ?? null,
                'official_url' => $s['official_url'] ?? null,
                'overview' => $s['overview'],
                'benefits' => $s['benefits'] ?? [],
                'grants' => $s['grants'] ?? [],
                'documents' => $s['documents'] ?? [],
                'application_route' => $s['application_route'] ?? null,
                'window_note' => $s['window_note'] ?? null,
                'source_note' => $s['source_note'] ?? null,
                'rules' => $s['rules'],
                'is_active' => true,
                'sort_order' => $i + 1,
                'verified_on' => $s['verified_on'] ?? $config['verified_on'] ?? null,   // per scholarship when its source was re-checked
            ]);
        }
    }
}
