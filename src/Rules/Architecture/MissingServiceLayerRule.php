<?php

namespace AtoAugustine\Beacon\Rules\Architecture;

use AtoAugustine\Beacon\Rules\AbstractRule;
use Illuminate\Support\Facades\File;

class MissingServiceLayerRule extends AbstractRule
{
    public function getId(): string
    {
        return 'architecture.missing-service-layer';
    }

    public function getCategory(): string
    {
        return 'architecture';
    }

    public function getSeverity(): string
    {
        return 'info';
    }

    public function getName(): string
    {
        return 'Service Layer Check';
    }

    public function getDescription(): string
    {
        return 'Checks if application uses service layer pattern for business logic separation';
    }

    public function scan(): array
    {
        $servicesPath = app_path('Services');
        $controllersPath = app_path('Http/Controllers');

        $hasServices = File::exists($servicesPath) && count(File::allFiles($servicesPath)) > 0;

        if (! File::exists($controllersPath)) {
            return $this->pass('Controllers directory not found');
        }

        $controllers = File::allFiles($controllersPath);
        $controllersWithBusinessLogic = [];

        foreach ($controllers as $controller) {
            $content = File::get($controller->getPathname());

            // Check for complex business logic patterns
            // Look for multiple method calls, conditionals, loops that suggest business logic
            $complexity = 0;

            // Count method calls (excluding standard Laravel methods)
            preg_match_all('/->(?!get|post|put|patch|delete|all|input|has|exists|validate|route|redirect|view|json|response)(\w+)\(/i', $content, $methodMatches);
            $complexity += count($methodMatches[0]);

            // Count conditionals and loops
            $complexity += substr_count($content, 'if (');
            $complexity += substr_count($content, 'foreach');
            $complexity += substr_count($content, 'while');

            // If controller has significant complexity, it might benefit from services
            if ($complexity > 10) {
                $lineCount = substr_count($content, "\n") + 1;
                $controllersWithBusinessLogic[] = [
                    'file' => $controller->getRelativePathname(),
                    'complexity_score' => $complexity,
                    'lines' => $lineCount,
                ];
            }
        }

        if (! $hasServices && ! empty($controllersWithBusinessLogic)) {
            return $this->suggest(
                'Found '.count($controllersWithBusinessLogic).' controller(s) with business logic - service layer not detected',
                [
                    'controllers' => $controllersWithBusinessLogic,
                    'recommendation' => 'Consider creating a Services directory and extracting business logic from controllers to service classes',
                ]
            );
        }

        if ($hasServices && ! empty($controllersWithBusinessLogic)) {
            return $this->suggest(
                'Some controllers still contain business logic - service layer exists but may not be fully utilized',
                [
                    'controllers' => $controllersWithBusinessLogic,
                    'recommendation' => 'Consider moving remaining business logic to service classes',
                ]
            );
        }

        return $this->pass('Service layer pattern appears to be in use or controllers are appropriately thin');
    }
}
