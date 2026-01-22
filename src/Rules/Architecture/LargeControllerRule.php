<?php

namespace Otatechie\Spotlight\Rules\Architecture;

use Otatechie\Spotlight\Rules\AbstractRule;
use Illuminate\Support\Facades\File;

class LargeControllerRule extends AbstractRule
{
    protected ?string $name = 'Controller Size Check';

    protected string $description = 'Identifies controllers that may benefit from refactoring';

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
