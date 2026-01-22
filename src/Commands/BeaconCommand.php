<?php

namespace AtoAugustine\Beacon\Commands;

use AtoAugustine\Beacon\Beacon;
use AtoAugustine\Beacon\Severity;
use Illuminate\Console\Command;

class BeaconCommand extends Command
{
    public $signature = 'beacon:scan
                        {--format=table : Output format (table, json)}
                        {--category=* : Specific categories to scan (performance, security, architecture)}
                        {--severity= : Minimum severity level (info, warning, critical)}
                        {--fail-on= : Exit with error code if issues found (critical, warning, suggestion)}';

    public $description = 'Scan Laravel application for performance, security, and architecture insights';

    public function handle(Beacon $beacon): int
    {
        $startTime = microtime(true);

        $this->displayHeader();

        $categories = $this->option('category');
        $format = $this->option('format');
        $failOn = $this->option('fail-on');

        $results = $beacon->scan($categories);

        if ($format === 'json') {
            $this->line(json_encode($results, JSON_PRETTY_PRINT));
        } else {
            $this->displayTableResults($results, microtime(true) - $startTime);
        }

        if ($failOn) {
            return $this->handleExitCode($results, $failOn);
        }

        return $this->handleExitCode($results, 'auto');
    }

    protected function displayHeader(): void
    {
        $this->info('Laravel Beacon Scan');
        $this->line('────────────────────');
        $this->newLine();

        $env = app()->environment();
        $laravelVersion = app()->version();

        $this->line("Environment: {$env}");
        $this->line("Laravel Version: {$laravelVersion}");
        $this->newLine();
    }

    protected function handleExitCode(array $results, string $failOn): int
    {
        $summary = $results['summary'];
        $critical = $summary['critical'] ?? 0;
        $high = $summary['high'] ?? 0;
        $medium = $summary['medium'] ?? 0;
        $low = $summary['low'] ?? 0;

        // Handle explicit fail-on option
        if ($failOn !== 'auto') {
            return match ($failOn) {
                'critical' => $critical > 0 ? 3 : 0,
                'high' => ($critical > 0 || $high > 0) ? 2 : 0,
                'medium' => ($critical > 0 || $high > 0 || $medium > 0) ? 2 : 0,
                'low' => ($critical > 0 || $high > 0 || $medium > 0 || $low > 0) ? 1 : 0,
                default => 0,
            };
        }

        // Default exit code system: 0 = clean, 1 = low only, 2 = medium/high, 3 = critical
        if ($critical > 0) {
            return 3;
        }
        if ($high > 0 || $medium > 0) {
            return 2;
        }
        if ($low > 0) {
            return 1;
        }

        return 0;
    }

    protected function displayTableResults(array $results, float $duration): void
    {
        $summary = $results['summary'];

        $this->info('Summary');
        $this->line('────────────────────');

        // Build colored table rows
        $rows = [];
        $critical = $summary['critical'] ?? 0;
        $high = $summary['high'] ?? 0;
        $medium = $summary['medium'] ?? 0;
        $low = $summary['low'] ?? 0;

        $rows[] = [$this->colorizeSeverity('Critical', 'critical'), $critical];
        $rows[] = [$this->colorizeSeverity('High', 'high'), $high];
        $rows[] = [$this->colorizeSeverity('Medium', 'medium'), $medium];
        $rows[] = [$this->colorizeSeverity('Low', 'low'), $low];

        $this->table(['Severity', 'Count'], $rows);

        $healthScore = $summary['health_score'] ?? 100;
        $this->newLine();
        $this->line("Health Score: {$healthScore}%");
        $this->newLine();

        foreach ($results['categories'] as $category => $categoryData) {
            $this->displayCategoryResults($category, $categoryData);
        }

        $this->newLine();
        $this->comment('Scan completed in '.number_format($duration, 2).'s');
    }

    protected function displayCategoryResults(string $category, array $categoryData): void
    {
        $categoryName = $categoryData['name'];
        $rules = $categoryData['rules'] ?? [];

        // Filter to only show issues (not passed rules)
        $issues = array_filter($rules, fn ($rule) => ($rule['status'] ?? '') !== 'passed');

        if (empty($issues)) {
            return; // Skip categories with no issues
        }

        $this->info("{$categoryName} Issues");
        $this->line('────────────────────');

        // Group by severity
        $grouped = [];
        foreach ($issues as $rule) {
            $severity = $rule['severity'] ?? 'low';
            if (! isset($grouped[$severity])) {
                $grouped[$severity] = [];
            }
            $grouped[$severity][] = $rule;
        }

        // Display in severity order: critical, high, medium, low
        $severityOrder = ['critical', 'high', 'medium', 'low'];
        foreach ($severityOrder as $severity) {
            if (! isset($grouped[$severity])) {
                continue;
            }

            foreach ($grouped[$severity] as $rule) {
                $this->displayRuleResult($rule);
            }
        }

        $this->newLine();
    }

    protected function displayRuleResult(array $ruleResult): void
    {
        $severity = $ruleResult['severity'] ?? 'low';
        $type = $ruleResult['type'] ?? 'advisory';
        $message = $ruleResult['message'] ?? '';

        // Get severity label and emoji
        $severityLabel = strtoupper($severity);
        $emoji = Severity::emoji($severity);

        // Colorize the severity label
        $coloredSeverity = $this->colorizeSeverity($severityLabel, $severity);

        // Format based on type
        if ($type === 'objective') {
            // Objective: Show severity clearly
            $this->line("{$emoji} [{$coloredSeverity}] {$message}");
        } else {
            // Advisory: Gentler tone
            $this->line("{$emoji} [{$coloredSeverity}] {$message}");
        }

        // Display file locations if available
        $this->displayFileLocations($ruleResult['metadata'] ?? []);

        // Show recommendation if available
        if (isset($ruleResult['metadata']['recommendation'])) {
            $this->comment("→ {$ruleResult['metadata']['recommendation']}");
        }
    }

    protected function displayFileLocations(array $metadata): void
    {
        $locations = $this->extractFileLocations($metadata);
        
        if (empty($locations)) {
            return;
        }

        // Limit to first 5 locations to avoid overwhelming output
        $displayLocations = array_slice($locations, 0, 5);
        
        foreach ($displayLocations as $location) {
            $file = $this->formatFilePath($location['file'] ?? '');
            $line = $location['line'] ?? null;
            
            if ($line) {
                $this->line("  <fg=gray>at {$file}:{$line}</>");
            } else {
                $this->line("  <fg=gray>at {$file}</>");
            }
        }
        
        // Show count if there are more locations
        if (count($locations) > 5) {
            $remaining = count($locations) - 5;
            $this->line("  <fg=gray>... and {$remaining} more location(s)</>");
        }
    }

    protected function formatFilePath(string $file): string
    {
        // If file path doesn't start with app/, resources/, etc., try to infer
        // Rules typically return paths like "Http/Controllers/UserController.php"
        // or "Models/User.php" - we want to show them as "app/Http/Controllers/..."
        
        if (str_starts_with($file, 'app/') || 
            str_starts_with($file, 'resources/') || 
            str_starts_with($file, 'config/') ||
            str_starts_with($file, 'routes/') ||
            str_starts_with($file, 'database/')) {
            return $file;
        }
        
        // If it looks like a relative path from app directory
        if (str_contains($file, 'Http/Controllers') || 
            str_contains($file, 'Models/') ||
            str_contains($file, 'Services/') ||
            str_contains($file, 'Repositories/')) {
            return "app/{$file}";
        }
        
        // If it looks like a view path
        if (str_contains($file, 'views/') || str_contains($file, '.blade.php')) {
            return "resources/{$file}";
        }
        
        // Return as-is if we can't determine
        return $file;
    }

    protected function extractFileLocations(array $metadata): array
    {
        $locations = [];

        // Handle different metadata structures
        // Pattern 1: 'issues' array with 'file' and 'line' keys
        if (isset($metadata['issues']) && is_array($metadata['issues'])) {
            foreach ($metadata['issues'] as $issue) {
                if (isset($issue['file'])) {
                    $locations[] = [
                        'file' => $issue['file'],
                        'line' => $issue['line'] ?? null,
                    ];
                }
            }
        }

        // Pattern 2: 'files' array with 'file' key (and optional 'line')
        if (isset($metadata['files']) && is_array($metadata['files'])) {
            foreach ($metadata['files'] as $file) {
                if (is_string($file)) {
                    $locations[] = ['file' => $file, 'line' => null];
                } elseif (isset($file['file'])) {
                    $locations[] = [
                        'file' => $file['file'],
                        'line' => $file['line'] ?? null,
                    ];
                }
            }
        }

        // Pattern 3: 'instantiations' array with 'file' and 'line' keys
        if (isset($metadata['instantiations']) && is_array($metadata['instantiations'])) {
            foreach ($metadata['instantiations'] as $instantiation) {
                if (isset($instantiation['file'])) {
                    $locations[] = [
                        'file' => $instantiation['file'],
                        'line' => $instantiation['line'] ?? null,
                    ];
                }
            }
        }

        // Pattern 4: 'controllers' array with 'file' key
        if (isset($metadata['controllers']) && is_array($metadata['controllers'])) {
            foreach ($metadata['controllers'] as $controller) {
                if (isset($controller['file'])) {
                    $locations[] = [
                        'file' => $controller['file'],
                        'line' => $controller['line'] ?? null,
                    ];
                }
            }
        }

        // Pattern 5: Direct 'file' key (single file)
        if (isset($metadata['file'])) {
            $locations[] = [
                'file' => $metadata['file'],
                'line' => $metadata['line'] ?? null,
            ];
        }

        // Remove duplicates and return
        $unique = [];
        foreach ($locations as $location) {
            $key = $location['file'].':'.($location['line'] ?? '');
            if (! isset($unique[$key])) {
                $unique[$key] = $location;
            }
        }

        return array_values($unique);
    }

    protected function colorizeSeverity(string $text, string $severity): string
    {
        // Check if color output is enabled
        if (! $this->output->isDecorated()) {
            return $text;
        }

        return match ($severity) {
            'critical' => "<fg=red>{$text}</>",
            'high' => "<fg=yellow>{$text}</>",
            'medium' => "<fg=cyan>{$text}</>",
            'low' => "<fg=blue>{$text}</>",
            default => $text,
        };
    }
}
