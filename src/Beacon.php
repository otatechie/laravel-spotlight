<?php

namespace AtoAugustine\Beacon;

use AtoAugustine\Beacon\Rules\RuleInterface;
use AtoAugustine\Beacon\Rules\RuleRegistry;
use Illuminate\Support\Facades\Log;

class Beacon
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
            ],
        ];

        $rulesToRun = empty($categories)
            ? $this->registry->all()
            : $this->getRulesByCategories($categories);

        foreach ($rulesToRun as $rule) {
            $ruleResult = $this->executeRule($rule);
            $results['rules'][$rule->getId()] = $ruleResult;
            $results['summary']['total_rules']++;

            // Update summary counts
            if ($ruleResult['status'] === 'passed') {
                $results['summary']['passed']++;
            } elseif ($ruleResult['status'] === 'suggestion' || $ruleResult['status'] === 'failed') {
                // Support both 'suggestion' (new) and 'failed' (legacy) statuses
                $results['summary']['suggestions']++;
            } elseif ($ruleResult['status'] === 'error') {
                $results['summary']['errors']++;
            }

            // Group by category
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

        return $results;
    }

    /**
     * Execute a rule safely with error handling
     *
     * @return array<string, mixed>
     */
    protected function executeRule(RuleInterface $rule): array
    {
        $debug = config('beacon.debug', false);

        try {
            if ($debug) {
                Log::debug("Beacon: Executing rule {$rule->getId()}");
            }

            $result = $rule->scan();

            if ($debug) {
                Log::debug("Beacon: Rule {$rule->getId()} completed with status: {$result['status']}");
            }

            return $result;
        } catch (\Throwable $e) {
            $errorHandling = config('beacon.error_handling', 'continue');

            if ($errorHandling === 'stop') {
                throw $e;
            }

            Log::warning("Beacon rule {$rule->getId()} encountered an issue: {$e->getMessage()}", [
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
