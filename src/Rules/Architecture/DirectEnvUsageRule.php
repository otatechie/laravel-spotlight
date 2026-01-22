<?php

namespace Otatechie\Spotlight\Rules\Architecture;

use Otatechie\Spotlight\Rules\AbstractRule;
use Illuminate\Support\Facades\File;

class DirectEnvUsageRule extends AbstractRule
{
    protected ?string $name = 'Direct ENV Usage Check';

    protected string $description = 'Identifies direct env() usage that should use config() instead';

    public function scan(): array
    {
        $appPath = app_path();

        if (! File::exists($appPath)) {
            return $this->pass('App directory not found');
        }

        $files = File::allFiles($appPath);
        $filesWithEnv = [];

        foreach ($files as $file) {
            $content = File::get($file->getPathname());

            // Check for direct env() calls (excluding config files and .env.example)
            if (preg_match('/env\s*\(/i', $content)) {
                // Count occurrences
                preg_match_all('/env\s*\(/i', $content, $matches);
                $count = count($matches[0]);

                // Exclude config files (they're allowed to use env())
                if (! str_contains($file->getPathname(), 'config')) {
                    $filesWithEnv[] = [
                        'file' => $file->getRelativePathname(),
                        'count' => $count,
                    ];
                }
            }
        }

        if (! empty($filesWithEnv)) {
            $totalUsages = array_sum(array_column($filesWithEnv, 'count'));

            return $this->suggest(
                'Found '.count($filesWithEnv).' file(s) with direct env() usage ('.$totalUsages.' total)',
                [
                    'files' => $filesWithEnv,
                    'recommendation' => 'Use config() helper instead of env() directly. Add values to config files and access via config() for better performance and testability.',
                ]
            );
        }

        return $this->pass('No direct env() usage found in application code');
    }
}
