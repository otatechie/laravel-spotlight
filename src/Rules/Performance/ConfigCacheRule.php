<?php

namespace AtoAugustine\Beacon\Rules\Performance;

use AtoAugustine\Beacon\Rules\AbstractRule;

class ConfigCacheRule extends AbstractRule
{
    // All properties auto-detected from class name and namespace!
    // Only override what's different:

    protected ?string $name = 'Config Cache Check';

    protected string $description = 'Checks if config cache is enabled in production';
    // id: auto-generated as 'performance.config-cache'
    // category: auto-detected as 'performance' from namespace
    // severity: defaults to 'info'

    public function scan(): array
    {
        if (! app()->environment('production')) {
            return $this->pass('Not in production environment');
        }

        $configCached = file_exists(base_path('bootstrap/cache/config.php'));

        if (! $configCached) {
            return $this->suggest(
                'Config cache could improve performance in production',
                [
                    'recommendation' => 'Run `php artisan config:cache` to cache your configuration',
                ]
            );
        }

        return $this->pass('Config cache is enabled');
    }
}
