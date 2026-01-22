<?php

namespace Otatechie\Spotlight\Rules\Performance;

use Otatechie\Spotlight\Rules\AbstractRule;

class EventCacheRule extends AbstractRule
{
    protected ?string $name = 'Event Cache Check';

    protected string $description = 'Checks if event cache is enabled in production';

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
