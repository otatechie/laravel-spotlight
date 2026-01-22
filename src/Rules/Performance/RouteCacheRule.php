<?php

namespace Otatechie\Spotlight\Rules\Performance;

use Otatechie\Spotlight\Rules\AbstractRule;

class RouteCacheRule extends AbstractRule
{
    protected ?string $name = 'Route Cache Check';

    protected string $description = 'Checks if route cache is enabled in production';

    public function scan(): array
    {
        if (! app()->environment('production')) {
            return $this->pass('Not in production environment');
        }

        $routeCached = file_exists(base_path('bootstrap/cache/routes-v7.php'))
            || file_exists(base_path('bootstrap/cache/routes.php'));

        if (! $routeCached) {
            return $this->suggest(
                'Route cache could improve performance in production',
                [
                    'recommendation' => 'Run `php artisan route:cache` to cache your routes',
                ]
            );
        }

        return $this->pass('Route cache is enabled');
    }
}
