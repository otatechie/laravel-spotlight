<?php

namespace AtoAugustine\Beacon\Rules\Architecture;

use AtoAugustine\Beacon\Rules\AbstractRule;
use Illuminate\Support\Facades\File;

class LogicInRoutesRule extends AbstractRule
{
    protected ?string $name = 'Logic in Routes Check';

    protected string $description = 'Identifies complex logic in route files that should be in controllers';

    public function scan(): array
    {
        $routesPath = base_path('routes');

        if (! File::exists($routesPath)) {
            return $this->pass('Routes directory not found');
        }

        $routeFiles = File::glob($routesPath.'/*.php');
        $routesWithLogic = [];

        foreach ($routeFiles as $file) {
            $content = File::get($file);

            // Check for complex logic patterns in route closures
            $complexity = 0;

            // Count conditionals and loops (indicates logic)
            $complexity += substr_count($content, 'if (');
            $complexity += substr_count($content, 'foreach');
            $complexity += substr_count($content, 'while');

            // Count database queries
            $complexity += preg_match_all('/DB::|::(all|get|find|where|first)/i', $content);

            // Count method calls (excluding Route:: methods)
            preg_match_all('/->(?!get|post|put|patch|delete|name|middleware|group|prefix|where)(\w+)\(/i', $content, $matches);
            $complexity += count($matches[0]);

            if ($complexity > 5) {
                $routesWithLogic[] = [
                    'file' => basename($file),
                    'complexity_score' => $complexity,
                ];
            }
        }

        if (! empty($routesWithLogic)) {
            return $this->suggest(
                'Found '.count($routesWithLogic).' route file(s) with complex logic',
                [
                    'routes' => $routesWithLogic,
                    'recommendation' => 'Move complex logic from route closures to controller methods for better organization and testability',
                ]
            );
        }

        return $this->pass('Route files appear to be clean of complex logic');
    }
}
