<?php

namespace Otatechie\Spotlight\Rules\Performance;

use Otatechie\Spotlight\Rules\AbstractRule;
use Illuminate\Support\Facades\File;

class NPlusOneQueriesRule extends AbstractRule
{
    protected ?string $severity = 'high'; // Serious performance issue

    protected ?string $name = 'N+1 Query Detection';

    protected string $description = 'Identifies potential N+1 query problems in controllers and models';

    public function scan(): array
    {
        $controllersPath = app_path('Http/Controllers');
        $modelsPath = app_path('Models');

        $potentialIssues = [];

        // Check controllers
        if (File::exists($controllersPath)) {
            $controllers = File::allFiles($controllersPath);

            foreach ($controllers as $controller) {
                $content = File::get($controller->getPathname());
                $issues = $this->detectNPlusOnePatterns($content, $controller->getRelativePathname());
                $potentialIssues = array_merge($potentialIssues, $issues);
            }
        }

        // Check models for relationship access patterns
        if (File::exists($modelsPath)) {
            $models = File::allFiles($modelsPath);

            foreach ($models as $model) {
                $content = File::get($model->getPathname());
                $issues = $this->detectModelNPlusOnePatterns($content, $model->getRelativePathname());
                $potentialIssues = array_merge($potentialIssues, $issues);
            }
        }

        if (! empty($potentialIssues)) {
            return $this->suggest(
                'Found '.count($potentialIssues).' potential N+1 query pattern(s)',
                [
                    'issues' => $potentialIssues,
                    'recommendation' => 'Use eager loading (->with(), ->load()) to prevent N+1 queries. Review the identified patterns and add eager loading where relationships are accessed in loops.',
                ]
            );
        }

        return $this->pass('No obvious N+1 query patterns detected');
    }

    /**
     * Detect N+1 patterns in controllers
     */
    protected function detectNPlusOnePatterns(string $content, string $file): array
    {
        $issues = [];
        $lines = explode("\n", $content);

        // Pattern 1: Model queries without ->with() followed by foreach
        // Look for: $users = User::all(); foreach ($users as $user) { $user->posts }
        foreach ($lines as $lineNum => $line) {
            // Check for Model::all() or Model::get() without ->with()
            if (preg_match('/(\w+)::(all|get|where|find)\([^)]*\)(?!.*->with\()/i', $line, $matches)) {
                // Check if next few lines have a foreach loop
                $nextLines = array_slice($lines, $lineNum, 10);
                $nextContent = implode("\n", $nextLines);

                if (preg_match('/foreach\s*\([^)]*as\s+(\$\w+)\)\s*\{[^}]*\1->\w+/s', $nextContent)) {
                    $issues[] = [
                        'file' => $file,
                        'line' => $lineNum + 1,
                        'pattern' => 'Model query without eager loading followed by foreach loop',
                        'type' => 'controller',
                    ];
                }
            }
        }

        // Pattern 2: foreach loops accessing relationships
        // Look for: foreach ($items as $item) { $item->relationship->property }
        foreach ($lines as $lineNum => $line) {
            if (preg_match('/foreach\s*\([^)]*as\s+(\$\w+)\)/i', $line, $foreachMatch)) {
                $varName = $foreachMatch[1];
                // Check next 20 lines for relationship access
                $nextLines = array_slice($lines, $lineNum, 20);
                $nextContent = implode("\n", $nextLines);

                // Look for $var->relationship (but not $var->with())
                if (preg_match('/'.preg_quote($varName, '/').'->(?!with\()\w+/i', $nextContent)) {
                    // Check if there was eager loading before this foreach
                    $beforeLines = array_slice($lines, max(0, $lineNum - 10), 10);
                    $beforeContent = implode("\n", $beforeLines);

                    if (! preg_match('/->with\s*\(/i', $beforeContent)) {
                        $issues[] = [
                            'file' => $file,
                            'line' => $lineNum + 1,
                            'pattern' => 'foreach loop accessing relationships without eager loading',
                            'type' => 'controller',
                        ];
                    }
                }
            }
        }

        return $issues;
    }

    /**
     * Detect N+1 patterns in models
     */
    protected function detectModelNPlusOnePatterns(string $content, string $file): array
    {
        $issues = [];

        // Check if model has relationships
        $hasRelationships = preg_match('/function\s+\w+\s*\(\)\s*\{[^}]*return\s+\$this->(belongsTo|hasMany|hasOne|belongsToMany|morphTo|morphMany)/i', $content);

        if ($hasRelationships) {
            // Check for static methods that return collections without eager loading
            if (preg_match_all('/public\s+static\s+function\s+(\w+)\s*\([^)]*\)\s*\{([^}]+)\}/s', $content, $matches, PREG_OFFSET_CAPTURE)) {
                foreach ($matches[0] as $index => $match) {
                    $methodName = $matches[1][$index][0];
                    $methodBody = $matches[2][$index][0];

                    // Check if method returns models but doesn't use ->with()
                    if (preg_match('/::(all|get|where|find)/i', $methodBody) && ! str_contains($methodBody, '->with(')) {
                        $lineNumber = substr_count(substr($content, 0, $match[1]), "\n") + 1;
                        $issues[] = [
                            'file' => $file,
                            'line' => $lineNumber,
                            'pattern' => "Static method '{$methodName}' returns models without eager loading",
                            'type' => 'model',
                        ];
                    }
                }
            }
        }

        return $issues;
    }
}
