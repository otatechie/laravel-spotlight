<?php

namespace Otatechie\Spotlight\Rules\Architecture;

use Otatechie\Spotlight\Rules\AbstractRule;
use Illuminate\Support\Facades\File;

class RouteClosureUsageRule extends AbstractRule
{
    // Clean and DRY - only define what's different!
    protected ?string $name = 'Route Closure Check';

    protected string $description = 'Identifies route closures that may prevent route caching';
    // id: auto-generated as 'architecture.route-closure-usage'
    // category: auto-detected as 'architecture' from namespace
    // severity: defaults to 'info'

    public function scan(): array
    {
        $routesPath = base_path('routes');

        if (! File::exists($routesPath)) {
            return $this->pass('Routes directory not found');
        }

        $routeFiles = File::glob($routesPath.'/*.php');
        $closureCount = 0;
        $totalRoutes = 0;

        foreach ($routeFiles as $file) {
            $content = File::get($file);

            // Count Route:: closures
            preg_match_all('/Route::\w+\s*\([^)]*function\s*\(/i', $content, $matches);
            $closureCount += count($matches[0]);

            // Count total routes
            preg_match_all('/Route::\w+/i', $content, $routeMatches);
            $totalRoutes += count($routeMatches[0]);
        }

        if ($closureCount > 0) {
            return $this->suggest(
                "Found {$closureCount} route closure(s) - these prevent route caching",
                [
                    'closure_count' => $closureCount,
                    'total_routes' => $totalRoutes,
                    'recommendation' => 'Consider moving closures to controller methods if route caching is important for your app',
                ]
            );
        }

        return $this->pass('No route closures found');
    }
}
