<?php

namespace AtoAugustine\Beacon\Rules\Security;

use AtoAugustine\Beacon\Rules\AbstractRule;

class InsecureSessionDriverRule extends AbstractRule
{
    public function getId(): string
    {
        return 'security.session-driver';
    }

    public function getCategory(): string
    {
        return 'security';
    }

    public function getSeverity(): string
    {
        return 'info';
    }

    public function getName(): string
    {
        return 'Session Driver Check';
    }

    public function getDescription(): string
    {
        return 'Reviews session driver configuration';
    }

    public function scan(): array
    {
        $sessionDriver = config('session.driver', 'file');
        $productionRecommended = ['database', 'redis', 'memcached', 'file'];

        if (! in_array($sessionDriver, $productionRecommended)) {
            return $this->suggest(
                "Session driver '{$sessionDriver}' works for development, but may need review for production",
                [
                    'current_driver' => $sessionDriver,
                    'recommendation' => 'Consider using database, redis, or file driver for production environments',
                ]
            );
        }

        return $this->pass("Session driver '{$sessionDriver}' is configured");
    }
}
