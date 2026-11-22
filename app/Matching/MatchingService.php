<?php

namespace App\Matching;

/**
 * Runs every scholarship's rules against one set of answers. Pure PHP (no database), so it is
 * easy to test; ScholarshipApplicationService stores the output.
 */
final class MatchingService
{
    /**
     * @param  RuleEvaluator  $evaluator  checks one scholarship's rules against the facts
     */
    public function __construct(private RuleEvaluator $evaluator = new RuleEvaluator) {}

    /**
     * @param  array<string,mixed>  $answers  cleaned answers
     * @param  iterable<array{id:int|string,rules:array}>  $scholarships
     * @return array<int,array{scholarship_id:int|string,status:string,score:int,checks:array}>
     */
    public function evaluate(array $answers, iterable $scholarships): array
    {
        $facts = AnswerNormalizer::facts($answers);
        $rows = [];
        foreach ($scholarships as $s) {
            $r = $this->evaluator->evaluate($s['rules'], $facts);
            $rows[] = ['scholarship_id' => $s['id'], 'status' => $r['status'], 'score' => $r['score'], 'checks' => $r['checks']];
        }

        return $rows;
    }
}
