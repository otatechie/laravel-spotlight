<?php

namespace Otatechie\Spotlight;

use Illuminate\Support\Facades\Log;
use Otatechie\Spotlight\Rules\RuleInterface;
use Otatechie\Spotlight\Rules\RuleRegistry;

class Spotlight
{
    public function __construct(
        protected RuleRegistry $registry
    ) {}

    /**
     * Scan the Laravel application for insights
     *
     * @param  array<string>  $categories  Specific categories to scan (performance, security, architecture)
     * @return array<string, mixed>
     */
    public function scan(array $categories = []): array
    {
        $results = [
            'timestamp' => now()->toIso8601String(),
            'rules' => [],
            'categories' => [],
            'summary' => [
                'total_rules' => 0,
                'passed' => 0,
                'suggestions' => 0,
                'errors' => 0,
                'critical' => 0,
                'high' => 0,
                'medium' => 0,
                'low' => 0,
                'health_score' => 100,
            ],
        ];

        $rulesToRun = empty($categories)
            ? $this->registry->all()
            : $this->getRulesByCategories($categories);

        foreach ($rulesToRun as $rule) {
            $ruleResult = $this->executeRule($rule);
            $results['rules'][$rule->getId()] = $ruleResult;
            $results['summary']['total_rules']++;

            if ($ruleResult['status'] === 'passed') {
                $results['summary']['passed']++;
            } elseif ($ruleResult['status'] === 'suggestion' || $ruleResult['status'] === 'failed') {
                $results['summary']['suggestions']++;

                $severity = $ruleResult['severity'] ?? 'low';
                if (in_array($severity, ['critical', 'high', 'medium', 'low'], true)) {
                    $results['summary'][$severity]++;
                }
            } elseif ($ruleResult['status'] === 'error') {
                $results['summary']['errors']++;
            }

            $category = $rule->getCategory();
            if (! isset($results['categories'][$category])) {
                $results['categories'][$category] = [
                    'name' => ucfirst($category),
                    'rules' => [],
                    'passed' => 0,
                    'suggestions' => 0,
                    'errors' => 0,
                ];
            }

            $results['categories'][$category]['rules'][] = $ruleResult;

            if ($ruleResult['status'] === 'passed') {
                $results['categories'][$category]['passed']++;
            } elseif ($ruleResult['status'] === 'suggestion' || $ruleResult['status'] === 'failed') {
                $results['categories'][$category]['suggestions']++;
            } elseif ($ruleResult['status'] === 'error') {
                $results['categories'][$category]['errors']++;
            }
        }

        $results['summary']['health_score'] = $this->calculateHealthScore($results['summary']);

        return $results;
    }

    /**
     * Calculate health score based on severity weights
     *
     * @param  array<string, mixed>  $summary
     */
    protected function calculateHealthScore(array $summary): int
    {
        $totalRules = $summary['total_rules'] ?? 0;
        if ($totalRules === 0) {
            return 100;
        }

        $passed = $summary['passed'] ?? 0;
        $totalIssues = $totalRules - $passed;

        if ($totalIssues === 0) {
            return 100;
        }

        // Calculate weighted penalty
        $penalty = 0;
        $penalty += ($summary['critical'] ?? 0) * 100;
        $penalty += ($summary['high'] ?? 0) * 70;
        $penalty += ($summary['medium'] ?? 0) * 40;
        $penalty += ($summary['low'] ?? 0) * 10;

        // Normalize to 0-100 scale (assuming worst case: all rules are critical)
        $maxPenalty = $totalRules * 100;
        $score = max(0, 100 - (($penalty / $maxPenalty) * 100));

        return (int) round($score);
    }

    /**
     * Execute a rule safely with error handling
     *
     * @return array<string, mixed>
     */
    protected function executeRule(RuleInterface $rule): array
    {
        $debug = config('spotlight.debug', false);

        try {
            if ($debug) {
                Log::debug("Spotlight: Executing rule {$rule->getId()}");
            }

            $result = $rule->scan();

            if ($debug) {
                Log::debug("Spotlight: Rule {$rule->getId()} completed with status: {$result['status']}");
            }

            return $result;
        } catch (\Throwable $e) {
            $errorHandling = config('spotlight.error_handling', 'continue');

            if ($errorHandling === 'stop') {
                throw $e;
            }

            Log::warning("Spotlight rule {$rule->getId()} encountered an issue: {$e->getMessage()}", [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->getErrorResult($rule, $e);
        }
    }

    /**
     * Get default error result when a rule throws an exception
     *
     * @return array<string, mixed>
     */
    protected function getErrorResult(RuleInterface $rule, \Throwable $exception): array
    {
        return [
            'id' => $rule->getId(),
            'status' => 'error',
            'message' => "Rule could not complete: {$exception->getMessage()}",
            'severity' => $rule->getSeverity(),
            'category' => $rule->getCategory(),
            'metadata' => [
                'exception' => get_class($exception),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ],
        ];
    }

    /**
     * Get rules by categories
     *
     * @param  array<string>  $categories
     * @return array<string, RuleInterface>
     */
    protected function getRulesByCategories(array $categories): array
    {
        $rules = [];

        foreach ($categories as $category) {
            $categoryRules = $this->registry->byCategory($category);
            $rules = array_merge($rules, $categoryRules);
        }

        return $rules;
    }
}
