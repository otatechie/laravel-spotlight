<?php

namespace AtoAugustine\Beacon\Rules\Performance;

use AtoAugustine\Beacon\Rules\AbstractRule;

class ViewCacheRule extends AbstractRule
{
    public function getId(): string
    {
        return 'performance.view-cache';
    }

    public function getCategory(): string
    {
        return 'performance';
    }

    public function getSeverity(): string
    {
        return 'info';
    }

    public function getName(): string
    {
        return 'View Cache Check';
    }

    public function getDescription(): string
    {
        return 'Checks if view cache is enabled in production';
    }

    public function scan(): array
    {
        if (! app()->environment('production')) {
            return $this->pass('Not in production environment');
        }

        $viewCached = file_exists(base_path('bootstrap/cache/compiled.php'));

        if (! $viewCached) {
            return $this->suggest(
                'View cache could improve performance in production',
                [
                    'recommendation' => 'Run `php artisan view:cache` to pre-compile your Blade templates',
                ]
            );
        }

        return $this->pass('View cache is enabled');
    }
}
