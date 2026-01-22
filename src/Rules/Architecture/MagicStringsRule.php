<?php

namespace Otatechie\Spotlight\Rules\Architecture;

use Illuminate\Support\Facades\File;
use Otatechie\Spotlight\Rules\AbstractRule;

class MagicStringsRule extends AbstractRule
{
    protected ?string $name = 'Magic Strings Check';

    protected string $description = 'Identifies hardcoded strings that should be constants or config values';

    public function scan(): array
    {
        $appPath = app_path();

        if (! File::exists($appPath)) {
            return $this->pass('App directory not found');
        }

        $files = File::allFiles($appPath);
        $filesWithMagicStrings = [];

        foreach ($files as $file) {
            $content = File::get($file->getPathname());

            // Skip vendor and test files
            if (str_contains($file->getPathname(), 'vendor') ||
                str_contains($file->getPathname(), 'Tests')) {
                continue;
            }

            // Check for common magic string patterns
            // 1. Status strings: 'active', 'inactive', 'pending', 'published', etc.
            // 2. Type strings: 'admin', 'user', 'guest', etc.
            // 3. Comparison with hardcoded strings in conditionals

            $magicStringPatterns = [
                // Status comparisons
                '/==\s*[\'"]\s*(active|inactive|pending|published|draft|deleted|archived)\s*[\'"]/i',
                '/===\s*[\'"]\s*(active|inactive|pending|published|draft|deleted|archived)\s*[\'"]/i',
                '/->\w+\s*==\s*[\'"]\s*(active|inactive|pending|published|draft|deleted|archived)\s*[\'"]/i',

                // Type/role comparisons
                '/==\s*[\'"]\s*(admin|user|guest|moderator|super_admin)\s*[\'"]/i',
                '/===\s*[\'"]\s*(admin|user|guest|moderator|super_admin)\s*[\'"]/i',

                // Common config-like strings that should use config()
                '/[\'"]\s*(api_key|secret|password|token|url)\s*[\'"]\s*=>/i',
            ];

            $foundMagicStrings = [];
            foreach ($magicStringPatterns as $pattern) {
                if (preg_match_all($pattern, $content, $matches)) {
                    foreach ($matches[1] as $match) {
                        if (! in_array($match, $foundMagicStrings)) {
                            $foundMagicStrings[] = $match;
                        }
                    }
                }
            }

            if (! empty($foundMagicStrings)) {
                $filesWithMagicStrings[] = [
                    'file' => $file->getRelativePathname(),
                    'magic_strings' => $foundMagicStrings,
                ];
            }
        }

        if (! empty($filesWithMagicStrings)) {
            return $this->suggest(
                'Found '.count($filesWithMagicStrings).' file(s) with potential magic strings',
                [
                    'files' => $filesWithMagicStrings,
                    'recommendation' => 'Replace magic strings with class constants or config values. Example: Use Model::STATUS_ACTIVE instead of \'active\', or config(\'app.key\') instead of hardcoded values.',
                ]
            );
        }

        return $this->pass('No obvious magic strings detected');
    }
}
