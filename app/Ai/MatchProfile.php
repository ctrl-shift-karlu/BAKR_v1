<?php

namespace App\Ai;

use App\Matching\ApplicationSchema;

/**
 * The ONLY student data the Gemini AI matcher receives (owner's choice 2026-11-19: "de-identified only").
 *
 * - Never: name, e-mail, mobile number, street address / barangay, exact birth date, account or student number.
 *   Fields marked 'ai' => false in ApplicationSchema are skipped; birth date becomes an age.
 * - Free text (the form has none today; this guards any text field added later) is scrubbed: e-mail addresses, phone-like numbers, links
 *   and the student's own name parts become placeholders, and every text is length-capped.
 * - Answers are given as readable labels ("Up to ₱250,000"), not internal codes, so the AI reads
 *   what the student actually chose.
 */
final class MatchProfile
{
    /** Per free-text field, in characters, after scrubbing. */
    private const TEXT_CAP = 1500;

    /**
     * @param  array<string,mixed>  $answers  saved (sanitized + enriched) answers
     * @param  string|null  $studentName  only used to REMOVE the name from free text, never sent
     * @return array<string, array<string,mixed>> section title => [question label => answer]
     */
    public static function build(array $answers, ?string $studentName = null, ?\DateTimeInterface $today = null): array
    {
        $profile = [];
        $nameParts = self::nameParts($studentName);

        foreach (ApplicationSchema::sections() as $section) {
            $rows = [];
            foreach ($section['fields'] as $key => $f) {
                if ($key === 'birth_date') {
                    $age = ApplicationSchema::age($answers['birth_date'] ?? null, $today);
                    if ($age !== null) {
                        $rows['Age'] = $age;
                    }

                    continue;
                }
                if (($f['ai'] ?? true) === false || $key === 'college_gwa_scale') {
                    continue;
                }
                $shown = ApplicationSchema::shownAnswer($key, $answers);
                if ($shown === null || $shown === '') {
                    continue;
                }
                if (in_array($f['type'], ['text', 'textarea'], true)) {
                    $shown = self::scrub($shown, $nameParts);
                    if ($shown === '') {
                        continue;
                    }
                }
                $rows[$f['label']] = $shown;
            }
            if ($rows !== []) {
                $profile[$section['title']] = $rows;
            }
        }

        // Broad field of the program (config/program_fields.php) helps with field-specific scholarships.
        if (! empty($answers['program_field'])) {
            $profile['Educational Background']['Broad field of the program'] = ApplicationSchema::optionLabel('program_field', $answers['program_field']);
        }
        if (isset($answers['college_gwa']) && ($answers['college_gwa_scale'] ?? 'percent') === 'dnsc') {
            $profile['Academic Standing and Conduct']['Estimated college GWA in percent'] = ApplicationSchema::dnscToPercent((float) $answers['college_gwa']);
        }

        return $profile;
    }

    /** Remove contact details, links and the student's own name from free text. */
    public static function scrub(string $text, array $nameParts = []): string
    {
        $text = preg_replace('/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/iu', '[e-mail removed]', $text) ?? '';
        $text = preg_replace('~\b(?:https?://|www\.)\S+~iu', '[link removed]', $text) ?? '';
        $text = preg_replace('/(?<![\w.])\+?\d[\d\s\-()]{8,}\d/u', '[number removed]', $text) ?? '';
        foreach ($nameParts as $part) {
            $text = preg_replace('/\b'.preg_quote($part, '/').'\b/iu', '[name]', $text) ?? '';
        }

        return mb_substr(trim($text), 0, self::TEXT_CAP);
    }

    /** Name words of 3+ letters ("Juana", "Dela", "Cruz"); short particles stay. */
    private static function nameParts(?string $name): array
    {
        if (! $name) {
            return [];
        }
        $parts = preg_split('/[\s,.\-]+/u', $name) ?: [];

        return array_values(array_unique(array_filter($parts, fn ($p) => mb_strlen($p) >= 3)));
    }
}
