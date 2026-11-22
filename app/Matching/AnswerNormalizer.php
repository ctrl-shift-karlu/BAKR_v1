<?php

namespace App\Matching;

/** Turns raw form answers into the "facts" the rules read (percentages, income range, flags). */
final class AnswerNormalizer
{
    /** @param array<string,mixed> $answers */
    public static function facts(array $answers): array
    {
        $f = $answers;

        $f['dnsc_student'] = 'yes';   // BAKR accounts are for DNSC students (current or incoming)
        $f['citizenship'] = $answers['citizenship'] ?? null;
        $f['year_level'] = isset($answers['year_level']) ? (int) $answers['year_level'] : null;
        // "Not sure" is not an answer: the rule says "needs info" instead of treating it as "no" (2026-11-19).
        $f['top_five'] = ($answers['top_five'] ?? null) === 'unsure' ? null : ($answers['top_five'] ?? null);
        // Age in years from the birth date (the date itself never leaves BAKR); BFAR's age limit reads this.
        $f['age'] = ApplicationSchema::age($answers['birth_date'] ?? null);
        // Davao del Norte resident (LANDBANK). Older forms have no province: stays null = "needs info".
        $province = $answers['home_province'] ?? null;
        $f['lives_in_davao_del_norte'] = isset(ApplicationSchema::PROVINCES[$province]) ? ($province === 'davao_del_norte' ? 'yes' : 'no') : null;
        // Not asked in the first year (nobody has overstayed yet): a first-year is within the normal length.
        $f['within_years'] = $answers['within_years'] ?? ($f['year_level'] === 1 ? 'yes' : null);

        $shs = self::num($answers['shs_gwa'] ?? null);
        $college = self::num($answers['college_gwa'] ?? null);
        if ($college !== null && ($answers['college_gwa_scale'] ?? 'percent') === 'dnsc') {
            $collegePct = $college >= 1 && $college <= 5 ? ApplicationSchema::dnscToPercent($college) : null;
            $f['college_gwa_estimated'] = true;
        } else {
            $collegePct = $college;
        }

        $f['shs_gwa_percent'] = $shs;
        $f['college_gwa_percent'] = $collegePct;
        // "Last semester or school": college grades once enrolled, otherwise Grade 12.
        $f['last_gwa_percent'] = ($answers['enrollment_status'] ?? null) === 'currently_enrolled' && $collegePct !== null ? $collegePct : ($shs ?? $collegePct);

        $bracket = ApplicationSchema::incomeBracket($answers['household_income'] ?? null);
        $f['income'] = $answers['household_income'] ?? null;   // rules read this; the bracket comes from the next lines
        $f['income_min'] = $bracket['min'] ?? null;
        $f['income_max'] = $bracket['max'] ?? null;
        $f['income_label'] = $bracket['label'] ?? null;

        foreach (['special_groups', 'existing_assistance', 'talent_categories', 'leadership_roles'] as $k) {
            $v = $answers[$k] ?? [];
            $f[$k] = is_array($v) ? array_values($v) : [];
        }
        // Added 2026-11-18: forms saved before this question have no answer, so it stays null ("unknown"),
        // not [] (which would read as "none of these" and wrongly fail LANDBANK / CoScho / BFAR).
        $farm = $answers['farm_fisher_family'] ?? null;
        $f['farm_fisher_family'] = is_array($farm) ? array_values($farm) : null;

        return $f;
    }

    /** A numeric answer as a float, or null when it is empty or not a number. */
    private static function num(mixed $v): ?float
    {
        return is_numeric($v) ? (float) $v : null;
    }
}
