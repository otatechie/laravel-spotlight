<?php

namespace Otatechie\Spotlight\Rules\Architecture;

use Otatechie\Spotlight\Rules\AbstractRule;
use Illuminate\Support\Facades\File;

class DirectInstantiationRule extends AbstractRule
{
    protected ?string $name = 'Direct Class Instantiation Check';

    protected string $description = 'Identifies direct class instantiation that should use dependency injection';

    public function scan(): array
    {
        $appPath = app_path();

        if (! File::exists($appPath)) {
            return $this->pass('App directory not found');
        }

        $files = File::allFiles($appPath);
        $filesWithInstantiation = [];

        foreach ($files as $file) {
            $content = File::get($file->getPathname());

            // Skip vendor, tests, and config files
            if (str_contains($file->getPathname(), 'vendor') ||
                str_contains($file->getPathname(), 'Tests') ||
                str_contains($file->getPathname(), 'config')) {
                continue;
            }

            // Check for direct instantiation: new ClassName(
            // Exclude common Laravel patterns like new Request(), new Collection(), etc.
            if (preg_match_all('/new\s+([A-Z]\w+)\s*\(/i', $content, $matches, PREG_OFFSET_CAPTURE)) {
                foreach ($matches[1] as $index => $match) {
                    $className = $match[0];
                    $offset = $match[1];

                    // Skip Laravel built-in classes that are commonly instantiated
                    $allowedClasses = [
                        'Request', 'Response', 'Collection', 'Carbon', 'DateTime',
                        'Exception', 'InvalidArgumentException', 'ModelNotFoundException',
                        'RedirectResponse', 'View', 'JsonResponse', 'File', 'Storage',
                    ];

                    if (in_array($className, $allowedClasses)) {
                        continue;
                    }

                    // Check if it's in a constructor (dependency injection is preferred)
                    $beforeMatch = substr($content, max(0, $offset - 200), 200);
                    if (preg_match('/function\s+__construct\s*\(/i', $beforeMatch)) {
                        // Get line number
                        $lineNum = substr_count(substr($content, 0, $offset), "\n") + 1;

                        $filesWithInstantiation[] = [
                            'file' => $file->getRelativePathname(),
                            'line' => $lineNum,
                            'class' => $className,
                        ];
                    }
                }
            }
        }

        if (! empty($filesWithInstantiation)) {
            $uniqueFiles = array_unique(array_column($filesWithInstantiation, 'file'));

            return $this->suggest(
                'Found '.count($uniqueFiles).' file(s) with direct class instantiation ('.count($filesWithInstantiation).' total)',
                [
                    'instantiations' => $filesWithInstantiation,
                    'recommendation' => 'Use dependency injection via constructor instead of direct instantiation. This improves testability and follows Laravel best practices.',
                ]
            );
        }

        return $this->pass('No direct class instantiation issues found');
    }
}
