<?php

namespace AtoAugustine\Beacon\Rules\Performance;

use AtoAugustine\Beacon\Rules\AbstractRule;

class EventCacheRule extends AbstractRule
{
    public function getId(): string
    {
        return 'performance.event-cache';
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
        return 'Event Cache Check';
    }

    public function getDescription(): string
    {
        return 'Checks if event cache is enabled in production';
    }

    public function scan(): array
    {
        if (! app()->environment('production')) {
            return $this->pass('Not in production environment');
        }

        $eventCached = file_exists(base_path('bootstrap/cache/events.php'));

        if (! $eventCached) {
            return $this->suggest(
                'Event cache could improve performance in production',
                [
                    'recommendation' => 'Run `php artisan event:cache` to cache your event listeners',
                ]
            );
        }

        return $this->pass('Event cache is enabled');
    }
}
