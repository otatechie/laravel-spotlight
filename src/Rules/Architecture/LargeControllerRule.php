<?php

namespace AtoAugustine\Beacon\Rules\Architecture;

use AtoAugustine\Beacon\Rules\AbstractRule;
use Illuminate\Support\Facades\File;

class LargeControllerRule extends AbstractRule
{
    public function getId(): string
    {
        return 'architecture.large-controller';
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
        return 'Controller Size Check';
    }

    public function getDescription(): string
    {
        return 'Identifies controllers that may benefit from refactoring';
    }

    public function scan(): array
    {
        $controllersPath = app_path('Http/Controllers');

        if (! File::exists($controllersPath)) {
            return $this->pass('Controllers directory not found');
        }

        $controllers = File::allFiles($controllersPath);
        $largeControllers = [];
        $threshold = 300; // lines

        foreach ($controllers as $controller) {
            $content = File::get($controller->getPathname());
            $lineCount = substr_count($content, "\n") + 1;

            if ($lineCount > $threshold) {
                $largeControllers[] = [
                    'file' => $controller->getRelativePathname(),
                    'lines' => $lineCount,
                ];
            }
        }

        if (! empty($largeControllers)) {
            return $this->suggest(
                'Found '.count($largeControllers).' controller(s) over '.$threshold.' lines',
                [
                    'controllers' => $largeControllers,
                    'threshold' => $threshold,
                    'recommendation' => 'Consider extracting business logic to service classes or actions for easier maintenance',
                ]
            );
        }

        return $this->pass('All controllers are within recommended size');
    }
}
