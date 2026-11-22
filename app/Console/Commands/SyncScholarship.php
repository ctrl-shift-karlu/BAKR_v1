<?php

namespace App\Console\Commands;

use App\Models\Scholarship;
use App\Models\ScholarshipApplication;
use App\Services\ScholarshipApplicationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Copy ONE scholarship from config/scholarships.php into the database, for when its official
 * source was re-checked. Unlike ScholarshipSeeder it leaves the other scholarships (and their
 * provider edits) alone. It records a ScholarshipRevision (user = none, i.e. system) and, when
 * the rules changed, re-checks every student's current matches.
 *
 * Without --apply it only shows what would change.
 */
class SyncScholarship extends Command
{
    protected $signature = 'bakr:sync-scholarship {slug : e.g. ched-tdp} {--apply : write the change (otherwise preview only)}';

    protected $description = 'Update one scholarship in the database from config/scholarships.php (preview unless --apply)';

    /** Fields the provider normally maintains in the portal: synced only when config lists them. */
    private const PROVIDER_OWNED = ['renewal_requirements', 'application_opens_on', 'application_closes_on', 'results_on'];

    private const DATES = ['verified_on', 'application_opens_on', 'application_closes_on', 'results_on'];

    private const FIELDS = ['name', 'provider', 'official_url', 'overview', 'benefits', 'grants', 'documents', 'application_route', 'window_note', 'source_note', 'rules', 'verified_on'];

    /**
     * Compares one scholarship in the database with config/scholarships.php and prints the differences.
     * With --apply it saves them (keeping provider-owned dates and maintaining documents), logs a revision
     * and re-matches every student; a slug that is not in the database yet is added instead.
     *
     * @return int Command::SUCCESS, or Command::FAILURE when the slug is not in config/scholarships.php
     */
    public function handle(ScholarshipApplicationService $applications): int
    {
        $slug = (string) $this->argument('slug');
        $config = collect(config('scholarships.scholarships'))->firstWhere('slug', $slug);
        $scholarship = Scholarship::where('slug', $slug)->first();

        if (! $config) {
            $this->error("No scholarship '{$slug}' in config/scholarships.php.");

            return self::FAILURE;
        }
        if (! $scholarship) {
            return $this->create($config, $applications);
        }

        // Renewal requirements are normally the provider's: only synced when config lists them.
        // Application dates likewise: only synced when the official document (config) gives them; otherwise the provider's dates stay.
        $fields = [...self::FIELDS, ...array_keys(array_intersect_key($config, array_flip(self::PROVIDER_OWNED)))];

        $new = [];
        foreach ($fields as $field) {
            $new[$field] = $field === 'verified_on'
                ? ($config['verified_on'] ?? config('scholarships.verified_on'))
                : ($config[$field] ?? (in_array($field, ['benefits', 'grants', 'documents', 'rules', 'renewal_requirements'], true) ? [] : null));
        }
        $old = [];
        foreach ($fields as $field) {
            $old[$field] = in_array($field, self::DATES, true) ? $scholarship->{$field}?->format('Y-m-d') : $scholarship->{$field};
        }

        $changed = array_values(array_filter($fields, fn ($f) => $old[$f] != $new[$f]));
        if ($changed === []) {
            $this->info("{$scholarship->name} already matches config/scholarships.php. Nothing to do.");

            return self::SUCCESS;
        }

        $this->line("<comment>{$scholarship->name}</comment>: these fields differ from config/scholarships.php");
        foreach ($changed as $field) {
            $this->line("  - {$field}");
        }
        $rulesChanged = in_array('rules', $changed, true);
        if ($rulesChanged) {
            $oldKeys = array_column($old['rules'] ?? [], 'key');
            $newKeys = array_column($new['rules'], 'key');
            $this->line('  rules removed: '.(implode(', ', array_diff($oldKeys, $newKeys)) ?: 'none'));
            $this->line('  rules added:   '.(implode(', ', array_diff($newKeys, $oldKeys)) ?: 'none'));
            $this->line('  current student forms to re-check: '.ScholarshipApplication::where('is_current', true)->count());
        }

        if (! $this->option('apply')) {
            $this->warn('Preview only. Run again with --apply to write it.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($scholarship, $new, $old) {
            $snapshot = fn (array $v) => array_intersect_key($v, array_flip(['overview', 'benefits', 'documents', 'renewal_requirements', 'application_route', 'window_note', 'application_opens_on', 'application_closes_on', 'results_on', 'official_url', 'rules']));
            $scholarship->fill($new)->save();
            $scholarship->revisions()->create([
                'user_id' => null,   // system update from the official source, not a provider edit
                'before' => $snapshot($old),
                'after' => $snapshot($new),
                'rules_changed' => $old['rules'] != $new['rules'],
            ]);
        });

        $rematched = 0;
        if ($rulesChanged) {
            ScholarshipApplication::where('is_current', true)->lazyById(100)->each(function ($application) use ($applications, &$rematched) {
                $applications->rematch($application);
                $rematched++;
            });
        }

        $this->info("Updated {$scholarship->name}. Re-checked {$rematched} student form(s).");

        return self::SUCCESS;
    }

    /**
     * A scholarship that is in config but not yet in the database (a newly added one): insert only that
     * row, like ScholarshipSeeder does for it, without touching any other scholarship or provider edit.
     * Then every current student form is re-checked so the new scholarship gets its matches.
     */
    private function create(array $config, ScholarshipApplicationService $applications): int
    {
        $this->line("<comment>{$config['name']}</comment> is new: it will be added to the database.");
        if (! $this->option('apply')) {
            $this->line('  current student forms to check against it: '.ScholarshipApplication::where('is_current', true)->count());
            $this->warn('Preview only. Run again with --apply to add it.');

            return self::SUCCESS;
        }

        $position = collect(config('scholarships.scholarships'))->search(fn ($s) => $s['slug'] === $config['slug']);
        $extra = array_intersect_key($config, array_flip(self::PROVIDER_OWNED));

        $scholarship = Scholarship::create($extra + [
            'slug' => $config['slug'],
            'name' => $config['name'],
            'sector' => $config['sector'],
            'provider' => $config['provider'] ?? null,
            'official_url' => $config['official_url'] ?? null,
            'overview' => $config['overview'],
            'benefits' => $config['benefits'] ?? [],
            'grants' => $config['grants'] ?? [],
            'documents' => $config['documents'] ?? [],
            'application_route' => $config['application_route'] ?? null,
            'window_note' => $config['window_note'] ?? null,
            'source_note' => $config['source_note'] ?? null,
            'rules' => $config['rules'],
            'is_active' => true,
            'sort_order' => $position === false ? Scholarship::max('sort_order') + 1 : $position + 1,
            'verified_on' => $config['verified_on'] ?? config('scholarships.verified_on'),
        ]);

        $rematched = 0;
        ScholarshipApplication::where('is_current', true)->lazyById(100)->each(function ($application) use ($applications, &$rematched) {
            $applications->rematch($application);
            $rematched++;
        });

        $this->info("Added {$scholarship->name}. Checked {$rematched} student form(s) against it.");

        return self::SUCCESS;
    }
}
