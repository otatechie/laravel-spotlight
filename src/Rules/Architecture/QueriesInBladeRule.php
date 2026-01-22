<?php

namespace AtoAugustine\Beacon\Rules\Architecture;

use AtoAugustine\Beacon\Rules\AbstractRule;
use Illuminate\Support\Facades\File;

class QueriesInBladeRule extends AbstractRule
{
    protected ?string $name = 'Queries in Blade Check';

    protected string $description = 'Identifies database queries executed in Blade templates';

    public function scan(): array
    {
        $viewsPath = resource_path('views');

        if (! File::exists($viewsPath)) {
            return $this->pass('Views directory not found');
        }

        $views = File::allFiles($viewsPath);
        $viewsWithQueries = [];

        foreach ($views as $view) {
            $content = File::get($view->getPathname());

            // Check for common query patterns in Blade
            $patterns = [
                '/\{\{\s*\w+::(all|get|find|where|first)\(/i',  // Model::all(), User::get()
                '/@foreach\s*\(\s*\w+::(all|get|find|where)/i', // @foreach(User::all())
                '/DB::(table|select|get|first)/i',              // DB::table()
            ];

            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $content)) {
                    preg_match_all($pattern, $content, $matches);
                    $count = count($matches[0]);

                    $viewsWithQueries[] = [
                        'file' => $view->getRelativePathname(),
                        'query_count' => $count,
                    ];
                    break; // Only count once per file
                }
            }
        }

        if (! empty($viewsWithQueries)) {
            $totalQueries = array_sum(array_column($viewsWithQueries, 'query_count'));

            return $this->suggest(
                'Found '.count($viewsWithQueries).' Blade template(s) with database queries ('.$totalQueries.' total)',
                [
                    'views' => $viewsWithQueries,
                    'recommendation' => 'Move queries to controllers and pass data to views. This prevents N+1 queries and improves performance.',
                ]
            );
        }

        return $this->pass('No database queries found in Blade templates');
    }
}
