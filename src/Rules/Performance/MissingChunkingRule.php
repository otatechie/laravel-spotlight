<?php

namespace Otatechie\Spotlight\Rules\Performance;

use Otatechie\Spotlight\Rules\AbstractRule;
use Illuminate\Support\Facades\File;

class MissingChunkingRule extends AbstractRule
{
    protected string $severity = 'high'; // Serious performance issue

    protected ?string $name = 'Missing Chunking for Large Datasets';

    protected string $description = 'Identifies potential memory issues from loading large datasets without chunking';

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
                $issues = $this->detectChunkingIssues($content, $controller->getRelativePathname());
                $potentialIssues = array_merge($potentialIssues, $issues);
            }
        }

        // Check models
        if (File::exists($modelsPath)) {
            $models = File::allFiles($modelsPath);

            foreach ($models as $model) {
                $content = File::get($model->getPathname());
                $issues = $this->detectChunkingIssues($content, $model->getRelativePathname());
                $potentialIssues = array_merge($potentialIssues, $issues);
            }
        }

        if (! empty($potentialIssues)) {
            return $this->suggest(
                'Found '.count($potentialIssues).' potential large dataset operation(s) without chunking',
                [
                    'issues' => $potentialIssues,
                    'recommendation' => 'Use ->chunk() or ->cursor() for large datasets to prevent memory issues. Example: Model::chunk(500, function ($items) { ... });',
                ]
            );
        }

        return $this->pass('No obvious chunking issues detected');
    }

    protected function detectChunkingIssues(string $content, string $file): array
    {
        $issues = [];
        $lines = explode("\n", $content);

        foreach ($lines as $lineNum => $line) {
            // Check for ->get() or ->all() on potentially large datasets
            // Look for patterns like: Model::all()->get(), Model::where(...)->get()
            if (preg_match('/(\w+)::(all|where|whereIn|whereHas|has)\([^)]*\)->get\(\)/i', $line, $matches)) {
                // Check if chunking is used nearby (within next 5 lines)
                $nextLines = array_slice($lines, $lineNum, 5);
                $nextContent = implode("\n", $nextLines);

                // If no chunk() or cursor() is found, it's a potential issue
                if (! preg_match('/->(chunk|cursor)\(/i', $nextContent)) {
                    $issues[] = [
                        'file' => $file,
                        'line' => $lineNum + 1,
                        'pattern' => 'Large dataset query without chunking',
                        'code' => trim($line),
                    ];
                }
            }

            // Check for foreach loops on potentially large collections
            // Look for: $items = Model::all(); foreach ($items as $item)
            if (preg_match('/\$(\w+)\s*=\s*\w+::(all|get|where)\([^)]*\)->get\(\)/i', $line, $varMatch)) {
                $varName = $varMatch[1];
                // Check if this variable is used in a foreach
                $nextLines = array_slice($lines, $lineNum, 10);
                $nextContent = implode("\n", $nextLines);

                if (preg_match('/foreach\s*\(\s*\$'.$varName.'\s+as/i', $nextContent)) {
                    $issues[] = [
                        'file' => $file,
                        'line' => $lineNum + 1,
                        'pattern' => 'Large dataset loaded into memory for iteration',
                        'code' => trim($line),
                    ];
                }
            }
        }

        return $issues;
    }
}
