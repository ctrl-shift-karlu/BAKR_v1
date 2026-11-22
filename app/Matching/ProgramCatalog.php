<?php

namespace App\Matching;

use App\Models\Campus;
use App\Models\Program;

/** Connects the student's campus/program choice (from the database) to the field the scholarship rules read. */
final class ProgramCatalog
{
    public const UNRECOGNIZED = 'unrecognized';

    /** Broad field for a program name. Falls back to "unrecognized", which the matching treats as "confirm with the provider". */
    public static function fieldFor(?string $programName, ?array $map = null): string
    {
        $name = trim((string) $programName);
        if ($name === '') {
            return self::UNRECOGNIZED;
        }
        foreach ($map ?? self::map() as [$pattern, $field]) {
            if (preg_match($pattern, $name) === 1) {
                return $field;
            }
        }

        return self::UNRECOGNIZED;
    }

    /**
     * Adds the readable names and the derived field to sanitized answers. Names are stored with the answers,
     * so an old result still shows the program the student really picked even if it is renamed later.
     *
     * @param  array<string,mixed>  $answers
     */
    public static function enrich(array $answers): array
    {
        $program = isset($answers['program_id'], $answers['campus_id'])
            ? Program::where('id', $answers['program_id'])->where('campus_id', $answers['campus_id'])->first()
            : null;
        $campus = $program ? Campus::find($program->campus_id) : null;

        $answers['program_name'] = $program?->program_name;
        $answers['campus_name'] = $campus?->campus_name;
        $answers['program_field'] = self::fieldFor($program?->program_name);
        $answers = array_filter($answers, fn ($v) => $v !== null);
        ksort($answers);

        return $answers;
    }

    /**
     * Every DNSC program name, A–Z: from the `programs` table, or, while that table is empty (it has
     * no seeder yet), from the DNSC program names the program_in rules list (config/scholarships.php
     * keeps them "as named in the database").
     *
     * @return list<string>
     */
    public static function names(): array
    {
        $names = Program::query()->distinct()->orderBy('program_name')->pluck('program_name')->filter()->values()->all();
        if ($names !== []) {
            return $names;
        }

        $fromRules = [];
        foreach ((array) config('scholarships.scholarships', []) as $s) {
            foreach ($s['rules'] ?? [] as $rule) {
                if (($rule['type'] ?? null) === 'program_in') {
                    array_push($fromRules, ...($rule['pass'] ?? []), ...($rule['maybe'] ?? []), ...($rule['not'] ?? []));
                }
            }
        }
        $fromRules = array_values(array_unique($fromRules));
        sort($fromRules, SORT_NATURAL | SORT_FLAG_CASE);

        return $fromRules;
    }

    /**
     * Campus name => the programs offered there, Main campus first. From the database; while the
     * campus tables are empty, from config/dnsc_campuses.php (the same official list).
     *
     * @return array<string, list<string>>
     */
    public static function byCampus(): array
    {
        $out = [];
        foreach (Program::query()->with('campus:id,campus_name')->orderBy('program_name')->get() as $p) {
            if ($p->campus) {
                $out[$p->campus->campus_name][] = $p->program_name;
            }
        }
        $config = (array) config('dnsc_campuses.campuses', []);
        if ($out === []) {
            foreach ($config as $name => $c) {
                $out[$name] = array_values($c['programs'] ?? []);
            }
        }
        $order = array_flip(array_keys($config));
        uksort($out, fn ($a, $b) => ($order[$a] ?? 99) <=> ($order[$b] ?? 99) ?: strcmp($a, $b));

        return $out;
    }

    /** The campuses that offer a program (any spelling the catalog understands). */
    public static function campusesFor(?string $program): array
    {
        $name = self::canonicalName($program);
        if ($name === null) {
            return [];
        }

        return array_keys(array_filter(self::byCampus(), fn (array $programs) => in_array($name, $programs, true)));
    }

    /** The campus of a program when exactly one campus offers it, else null. */
    public static function inferCampus(?string $program): ?string
    {
        $campuses = self::campusesFor($program);

        return count($campuses) === 1 ? $campuses[0] : null;
    }

    /** Is this program offered at this campus? (Both may be typed loosely.) */
    public static function offeredAt(?string $campus, ?string $program): bool
    {
        $campus = self::canonicalCampus($campus);

        return $campus !== null && in_array($campus, self::campusesFor($program), true);
    }

    /**
     * The official campus name for what someone typed or imported: "Samal", "samal campus", "Main",
     * "Panabo" … Null when nothing, or more than one campus, matches.
     */
    public static function canonicalCampus(?string $input): ?string
    {
        $words = self::campusWords((string) $input);
        if ($words === []) {
            return null;
        }
        $hits = [];
        foreach (array_keys(self::byCampus()) as $name) {
            if (array_diff($words, self::campusWords($name)) === []) {
                $hits[] = $name;
            }
        }

        return count($hits) === 1 ? $hits[0] : null;
    }

    /** "Main (Panabo Campus)" → ['main', 'panabo']. */
    private static function campusWords(string $name): array
    {
        $n = preg_replace('/[^a-z0-9]+/', ' ', mb_strtolower(trim($name)));

        return array_values(array_filter(explode(' ', $n), fn ($w) => $w !== '' && $w !== 'campus'));
    }

    /**
     * The official program name for what someone typed or imported: same name in any case and
     * spacing, or with "BS" / "BSEd" / "BSE" / "AB" short forms ("BS Information Technology"). Null if none.
     */
    public static function canonicalName(?string $input): ?string
    {
        $key = self::nameKey((string) $input);
        if ($key === '') {
            return null;
        }
        foreach (self::names() as $name) {
            if (self::nameKey($name) === $key) {
                return $name;
            }
        }

        return null;
    }

    /** A program name reduced for comparison: lower case, punctuation removed, "BS" / "BSEd" / "AB" spelled out. */
    private static function nameKey(string $name): string
    {
        $n = mb_strtolower(trim($name));
        $n = preg_replace('/[^a-z0-9]+/', ' ', $n);
        $n = preg_replace(['/^bsed\b/', '/^bse\b/', '/^bs\b/', '/^ab\b/'], ['bachelor of secondary education', 'bachelor of secondary education', 'bachelor of science in', 'bachelor of arts in'], trim($n));

        return trim(preg_replace('/\s+/', ' ', str_replace(['agroforestry'], ['agro forestry'], $n)));
    }

    /** The program-name patterns of config/program_fields.php (empty outside Laravel, e.g. in plain unit tests). */
    private static function map(): array
    {
        return function_exists('config') ? (array) config('program_fields', []) : [];
    }
}
