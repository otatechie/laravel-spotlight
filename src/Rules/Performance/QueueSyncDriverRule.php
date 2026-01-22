<?php

namespace AtoAugustine\Beacon\Rules\Performance;

use AtoAugustine\Beacon\Rules\AbstractRule;

class QueueSyncDriverRule extends AbstractRule
{
    public function getId(): string
    {
        return 'performance.queue-sync-driver';
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
        return 'Queue Driver Check';
    }

    public function getDescription(): string
    {
        return 'Checks if queue is using sync driver in production';
    }

    public function scan(): array
    {
        if (! app()->environment('production')) {
            return $this->pass('Not in production environment');
        }

        $queueDriver = config('queue.default', 'sync');

        if ($queueDriver === 'sync') {
            return $this->suggest(
                'Queue is using sync driver - jobs run immediately without background processing',
                [
                    'current_driver' => $queueDriver,
                    'recommendation' => 'Consider using database, redis, or sqs driver for background job processing',
                ]
            );
        }

        return $this->pass("Queue is using {$queueDriver} driver");
    }
}
