<?php

namespace App\Ai;

/**
 * Turns a rule-engine result into plain language. Implementations must never change the status
 * or add requirements; they only describe what the rules already found.
 */
interface Explainer
{
    /** @param array $ctx see ExplanationPrompt::matchContext() */
    public function explainMatch(array $ctx): ?string;

    /** @param array<int,array> $contexts one per scholarship */
    public function summarize(array $contexts): ?string;
}
