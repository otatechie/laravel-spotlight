<?php

namespace AtoAugustine\Beacon\Commands;

use AtoAugustine\Beacon\Beacon;
use AtoAugustine\Beacon\Rules\RuleRegistry;
use Illuminate\Console\Command;

class BeaconCommand extends Command
{
    public $signature = 'beacon:scan
                        {--format=table : Output format (table, json)}
                        {--category=* : Specific categories to scan (performance, security, architecture)}
                        {--severity= : Minimum severity level (info, warning, critical)}';

    public $description = 'Scan Laravel application for performance, security, and architecture insights';

    public function handle(Beacon $beacon): int
    {
        $this->info('🔦 Beacon - Laravel Application Scanner');
        $this->newLine();
        $this->comment('Analyzing your application...');
        $this->newLine();

        $categories = $this->option('category');
        $format = $this->option('format');

        $results = $beacon->scan($categories);

        if ($format === 'json') {
            $this->line(json_encode($results, JSON_PRETTY_PRINT));

            return self::SUCCESS;
        }

        $this->displayTableResults($results);

        return self::SUCCESS;
    }

    protected function displayTableResults(array $results): void
    {
        $summary = $results['summary'];

        $this->info('📊 Scan Results:');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Checks', $summary['total_rules']],
                ['✅ Passed', $summary['passed']],
                ['💡 Suggestions', $summary['suggestions']],
                ['⚠️  Errors', $summary['errors']],
            ]
        );

        $this->newLine();

        foreach ($results['categories'] as $category => $categoryData) {
            $categoryName = $categoryData['name'];
            $passed = $categoryData['passed'];
            $suggestions = $categoryData['suggestions'];
            $errors = $categoryData['errors'];

            $statusIcon = ($suggestions > 0 || $errors > 0) ? '📝' : '✅';
            $this->info("{$statusIcon} {$categoryName} ({$passed} passed, {$suggestions} suggestions)");

            if (! empty($categoryData['rules'])) {
                foreach ($categoryData['rules'] as $ruleResult) {
                    $this->displayRuleResult($ruleResult);
                }
            } else {
                $this->comment('  No checks executed');
            }

            $this->newLine();
        }
    }

    protected function displayRuleResult(array $ruleResult): void
    {
        $status = $ruleResult['status'];
        $severity = $ruleResult['severity'] ?? 'info';
        $message = $ruleResult['message'] ?? '';

        $icon = match ($status) {
            'passed' => '✅',
            'suggestion' => '💡',
            'error' => '⚠️',
            default => '📝',
        };

        $severityLabel = match ($severity) {
            'critical' => 'IMPORTANT',
            'warning' => 'CONSIDER',
            'info' => 'INFO',
            default => 'INFO',
        };

        $this->line("  {$icon} [{$severityLabel}] {$message}");

        if (isset($ruleResult['metadata']['recommendation'])) {
            $this->comment("     → {$ruleResult['metadata']['recommendation']}");
        }
    }
}
