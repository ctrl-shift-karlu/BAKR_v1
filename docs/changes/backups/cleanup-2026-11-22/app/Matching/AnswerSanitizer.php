<?php

namespace App\Matching;

/** Keeps only known fields, trims values, and drops answers that do not apply to the student's situation. */
final class AnswerSanitizer
{
    /** @param array<string,mixed> $input */
    public static function clean(array $input): array
    {
        $out = [];
        foreach (ApplicationSchema::fields() as $key => $field) {
            $v = $input[$key] ?? null;
            if ($field['type'] === 'checkbox') {
                $v = array_values(array_unique(array_map('strval', is_array($v) ? $v : [])));
                $options = array_map('strval', array_keys($field['options']));
                $v = array_values(array_intersect($v, $options));
                // "None of these" together with a real choice is contradictory: keep the real choices,
                // because hiding an existing scholarship could wrongly make the student look eligible.
                if (! empty($field['exclusive']) && count($v) > 1) {
                    $v = array_values(array_diff($v, [$field['exclusive']]));
                }
                sort($v);   // same choices in a different tick order are not a change
                // A required group is never submitted empty (min:1), so empty = never asked (a form saved
                // before the question existed, e.g. farm_fisher_family on 2026-11-18). Leave it out so the
                // rule says "needs info" instead of a false "not eligible".
                if ($v === [] && ! empty($field['required'])) {
                    continue;
                }
                $out[$key] = $v;

                continue;
            }
            if (is_array($v)) {
                continue;   // a tick-box value sent for a single-answer question
            }
            $v = is_string($v) ? trim($v) : $v;
            if ($v === '' || $v === null) {
                continue;
            }
            $out[$key] = $field['type'] === 'number' ? (float) $v + 0 : (string) $v;
        }

        // Incoming first-years are always in year 1.
        if (($out['enrollment_status'] ?? null) === 'incoming_first_year') {
            $out['year_level'] = '1';
        }
        // Keep only the answers that apply to this student's year level (senior high school questions for a
        // 2nd-year student, college grades for an incoming one): an answer left from an earlier form is dropped.
        foreach (ApplicationSchema::fields() as $key => $field) {
            if (isset($field['when']) && ! ApplicationSchema::applies($field['when'], $out)) {
                unset($out[$key]);
            }
        }
        if (isset($out['college_gwa']) && empty($out['college_gwa_scale'])) {
            $out['college_gwa_scale'] = 'percent';
        }

        ksort($out);

        return $out;
    }
}
