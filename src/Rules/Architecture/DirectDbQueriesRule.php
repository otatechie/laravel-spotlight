<?php

namespace Otatechie\Spotlight\Rules\Architecture;

use Illuminate\Support\Facades\File;
use Otatechie\Spotlight\Rules\AbstractRule;

class DirectDbQueriesRule extends AbstractRule
{
    protected ?string $name = 'Direct DB Queries Check';

    protected string $description = 'Identifies direct database queries in controllers that could use repositories or models';

    public function scan(): array
    {
        $controllersPath = app_path('Http/Controllers');

        if (! File::exists($controllersPath)) {
            return $this->pass('Controllers directory not found');
        }

        $controllers = File::allFiles($controllersPath);
        $controllersWithDbQueries = [];

        foreach ($controllers as $controller) {
            $content = File::get($controller->getPathname());

            // Check for DB facade usage (excluding imports)
            $hasDbQueries = preg_match('/DB::(table|select|insert|update|delete|raw|statement)/i', $content);

            if ($hasDbQueries) {
                // Count occurrences
                preg_match_all('/DB::(table|select|insert|update|delete|raw|statement)/i', $content, $matches);
                $count = count($matches[0]);

                $controllersWithDbQueries[] = [
                    'file' => $controller->getRelativePathname(),
                    'query_count' => $count,
                ];
            }
        }

        if (! empty($controllersWithDbQueries)) {
            $totalQueries = array_sum(array_column($controllersWithDbQueries, 'query_count'));

            return $this->suggest(
                'Found '.count($controllersWithDbQueries).' controller(s) with direct DB queries ('.$totalQueries.' total)',
                [
                    'controllers' => $controllersWithDbQueries,
                    'recommendation' => 'Consider using Eloquent models or repository pattern to separate data access logic from controllers',
                ]
            );
        }

        return $this->pass('No direct DB queries found in controllers');
    }
}
