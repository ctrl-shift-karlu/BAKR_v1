<?php

namespace App\Support;

use Illuminate\Validation\Rule;

/**
 * A person's name in parts (owner, 2026-11-23): first name and surname required, middle name and suffix optional.
 * Used for student accounts (registration), scholars (provider Add form, Excel/CSV import, profile) and the scholar
 * accounts made from them. The parts are stored in `first_name`, `middle_name`, `last_name`, `suffix`; the one-line
 * name (`users.name`, `scholars.full_name`) is always compose()d from them, so every page, search and sort that
 * reads the full name keeps working.
 */
class PersonName
{
    /** The four parts, in the order they are written. */
    public const FIELDS = ['first_name', 'middle_name', 'last_name', 'suffix'];

    /** Suffixes a person may have (stored value => shown text). */
    public const SUFFIXES = ['Jr.' => 'Jr.', 'Sr.' => 'Sr.', 'II' => 'II', 'III' => 'III', 'IV' => 'IV', 'V' => 'V'];

    /** Longest first / middle name or surname. */
    public const PART_MAX = 60;

    /** Letters (any language, ñ included), spaces, dots, apostrophes and hyphens; starts with a letter. */
    public const PATTERN = "/^[\pL\pM][\pL\pM\s.'\x{2019}\-]*$/u";

    /** Surname particles that belong to the surname when an old one-line name is split ("Juan Dela Cruz"). */
    private const PARTICLES = ['de', 'del', 'dela', 'de la', 'delos', 'de los', 'della', 'di', 'da', 'dos', 'das', 'san', 'sta', 'sta.', 'santa', 'santo', 'sto', 'sto.', 'van', 'von', 'la', 'le', 'mc', 'mac'];

    /**
     * Validation rules for the four parts. $required = false makes first name / surname optional too (never used
     * for a new record; kept for callers that validate a partial update).
     */
    public static function rules(bool $required = true): array
    {
        $part = fn (bool $needed) => [$needed ? 'required' : 'nullable', 'string', 'max:'.self::PART_MAX, 'regex:'.self::PATTERN];

        return [
            'first_name' => $part($required),
            'middle_name' => $part(false),
            'last_name' => $part($required),
            'suffix' => ['nullable', Rule::in(array_keys(self::SUFFIXES))],
        ];
    }

    /**
     * Messages for rules(). $who = "your" (a student about themself) or "the scholar's" (a provider).
     */
    public static function messages(string $who = 'your'): array
    {
        return [
            'first_name.required' => "Type {$who} first name.",
            'last_name.required' => "Type {$who} surname.",
            'first_name.regex' => 'Use letters only (spaces, dots, hyphens and apostrophes are fine).',
            'middle_name.regex' => 'Use letters only (spaces, dots, hyphens and apostrophes are fine).',
            'last_name.regex' => 'Use letters only (spaces, dots, hyphens and apostrophes are fine).',
            'suffix.in' => 'Choose a suffix from the list (Jr., Sr., II, III, IV, V), or leave it empty.',
        ];
    }

    /** Field names in plain words, for messages. */
    public static function attributes(): array
    {
        return ['first_name' => 'first name', 'middle_name' => 'middle name', 'last_name' => 'surname', 'suffix' => 'suffix'];
    }

    /** "jr", "JR.", "Junior", "3rd", "iii" → "Jr." / "III"; empty → null; anything else is returned as typed (and fails validation). */
    public static function normalizeSuffix(mixed $value): ?string
    {
        $v = trim((string) $value);
        if ($v === '') {
            return null;
        }
        $key = rtrim(mb_strtolower($v), '.');

        return match ($key) {
            'jr', 'junior' => 'Jr.',
            'sr', 'senior' => 'Sr.',
            'ii', '2nd' => 'II',
            'iii', '3rd' => 'III',
            'iv', '4th' => 'IV',
            'v', '5th' => 'V',
            default => $v,
        };
    }

    /** Trims and collapses inner spaces; empty → null. */
    public static function clean(mixed $value): ?string
    {
        $v = trim(preg_replace('/\s+/u', ' ', (string) $value));

        return $v === '' ? null : $v;
    }

    /**
     * The cleaned parts from validated input plus the composed one-line name.
     *
     * @return array{first_name: ?string, middle_name: ?string, last_name: ?string, suffix: ?string, full: string}
     */
    public static function parts(array $data): array
    {
        $p = [
            'first_name' => self::clean($data['first_name'] ?? null),
            'middle_name' => self::clean($data['middle_name'] ?? null),
            'last_name' => self::clean($data['last_name'] ?? null),
            'suffix' => self::normalizeSuffix($data['suffix'] ?? null),
        ];

        return $p + ['full' => self::compose($p['first_name'], $p['middle_name'], $p['last_name'], $p['suffix'])];
    }

    /** "Juan" "Andres" "Dela Cruz" "Jr." → "Juan Andres Dela Cruz Jr." (empty parts left out). */
    public static function compose(?string $first, ?string $middle, ?string $last, ?string $suffix): string
    {
        return collect([$first, $middle, $last, $suffix])->map(fn ($p) => self::clean($p))->filter()->implode(' ');
    }

    /**
     * Best guess at the parts of an OLD one-line name (used once by the migration and to pre-fill a form for a row
     * that has no parts yet): a trailing suffix is taken off, the surname is the last word together with any particle
     * before it ("Dela Cruz", "De los Santos"), everything before is the first name. The middle name cannot be told
     * apart from a second first name, so it is left empty for the person to fill in.
     *
     * @return array{first_name: ?string, middle_name: ?string, last_name: ?string, suffix: ?string}
     */
    public static function split(?string $full): array
    {
        $words = preg_split('/\s+/u', trim((string) $full), -1, PREG_SPLIT_NO_EMPTY);
        $out = ['first_name' => null, 'middle_name' => null, 'last_name' => null, 'suffix' => null];
        if (! $words) {
            return $out;
        }
        $last = end($words);
        $suffix = self::normalizeSuffix(rtrim($last, ','));
        if (count($words) > 1 && isset(self::SUFFIXES[$suffix])) {
            $out['suffix'] = $suffix;
            array_pop($words);
            $words[count($words) - 1] = rtrim($words[count($words) - 1], ',');
        }
        if (count($words) === 1) {
            $out['first_name'] = $words[0];

            return $out;
        }

        $surname = [array_pop($words)];
        // Particles right before the surname belong to it (keep at least one word as the first name).
        while (count($words) > 1) {
            $two = mb_strtolower(implode(' ', array_slice($words, -2)));
            if (count($words) > 2 && in_array($two, self::PARTICLES, true)) {
                array_unshift($surname, ...array_splice($words, -2));

                continue;
            }
            if (in_array(mb_strtolower(end($words)), self::PARTICLES, true)) {
                array_unshift($surname, array_pop($words));

                continue;
            }
            break;
        }
        $out['first_name'] = implode(' ', $words);
        $out['last_name'] = implode(' ', $surname);

        return $out;
    }
}
