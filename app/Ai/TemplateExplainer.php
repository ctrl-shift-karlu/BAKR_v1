<?php

namespace App\Ai;

/**
 * Built-in explanation written from the rule results. Used when no AI key is set or the AI is
 * unreachable, and always available as the "quick summary" on the results page.
 */
class TemplateExplainer implements Explainer
{
    /**
     * Writes the built-in explanation of one match from its rule outcomes (no AI, always available).
     *
     * @param  array  $ctx  ExplanationPrompt::matchContext() data
     */
    public function explainMatch(array $ctx): ?string
    {
        $name = $ctx['scholarship'];
        $by = fn (string ...$o) => array_values(array_map(fn ($c) => $c['requirement'], array_filter($ctx['checks'], fn ($c) => in_array($c['outcome'], $o, true))));
        $pass = $by('pass');
        $fail = array_values(array_filter($by('fail')));
        $confirm = $by('confirm');
        $unknown = $by('unknown');

        $head = match ($ctx['result']) {
            'eligible' => "You look like a strong match for the $name. Every requirement BAKR can check is met.",
            'possible' => "You may qualify for the $name. Nothing blocks you so far, but a few points need to be confirmed with the provider.",
            'needs_info' => "BAKR cannot tell yet whether you match the $name, because some answers are missing.",
            default => "The $name is not a match for you right now.",
        };

        $parts = [$head];
        if ($pass && $ctx['result'] !== 'not_eligible') {
            $parts[] = 'Working in your favor: '.$this->join(array_slice($pass, 0, 3)).'.';
        }
        if ($fail) {
            $parts[] = ($ctx['result'] === 'not_eligible' ? 'What is blocking you: ' : 'Not met: ').$this->join($fail).'.';
        }
        if ($unknown) {
            $parts[] = 'Answer these in your matching form: '.$this->join($unknown).'.';
        }
        if ($confirm) {
            $parts[] = 'Confirm with the provider: '.$this->join($confirm).'.';
        }
        if ($ctx['result'] !== 'not_eligible' && ! empty($ctx['documents_to_prepare'])) {
            $parts[] = 'You can start preparing: '.$this->join(array_slice($ctx['documents_to_prepare'], 0, 3)).'. BAKR does not collect documents. You apply through the official source.';
        }
        if ($ctx['result'] === 'not_eligible') {
            $parts[] = 'If your situation changes, update your answers and BAKR will check again.';
        }

        return implode("\n\n", $parts);
    }

    /**
     * Writes the built-in overview of all matches: how many fit, and which scholarships to look at first.
     *
     * @param  list<array>  $contexts  one ExplanationPrompt::matchContext() per match
     */
    public function summarize(array $contexts): ?string
    {
        $group = fn (string $s) => array_values(array_map(fn ($c) => $c['scholarship'], array_filter($contexts, fn ($c) => $c['result'] === $s)));
        $eligible = $group('eligible');
        $possible = $group('possible');
        $needs = $group('needs_info');

        $parts = [];
        if ($eligible) {
            $parts[] = 'You look like a strong match for '.$this->join($eligible).'.';
        }
        if ($possible) {
            $parts[] = 'You may also qualify for '.$this->join($possible).', pending confirmation with the provider.';
        }
        if (! $eligible && ! $possible) {
            $parts[] = 'No scholarship is a clear match with your current answers.';
        }
        if ($needs) {
            $parts[] = 'Answer the missing questions to check '.$this->join($needs).'.';
        }
        $parts[] = 'Open each result to see what helps you, what blocks you and what to prepare.';

        return implode(' ', $parts);
    }

    /** @param array<int,string> $items */
    private function join(array $items): string
    {
        $items = array_values($items);
        $n = count($items);

        return match (true) {
            $n === 0 => '',
            $n === 1 => $items[0],
            $n === 2 => $items[0].' and '.$items[1],
            default => implode(', ', array_slice($items, 0, -1)).', and '.$items[$n - 1],
        };
    }
}
