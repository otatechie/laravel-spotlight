<?php

namespace Otatechie\Spotlight\Rules\Security;

use Otatechie\Spotlight\Rules\AbstractRule;

class InsecureSessionDriverRule extends AbstractRule
{
    protected ?string $name = 'Session Driver Check';

    protected string $description = 'Reviews session driver configuration';

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
