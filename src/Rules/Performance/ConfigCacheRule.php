<?php

namespace Otatechie\Spotlight\Rules\Performance;

use Otatechie\Spotlight\Rules\AbstractRule;

class ConfigCacheRule extends AbstractRule
{
    protected ?string $name = 'Config Cache Check';

    protected string $description = 'Checks if config cache is enabled in production';

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
