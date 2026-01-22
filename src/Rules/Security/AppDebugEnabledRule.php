<?php

namespace AtoAugustine\Beacon\Rules\Security;

use AtoAugustine\Beacon\Rules\AbstractRule;

class AppDebugEnabledRule extends AbstractRule
{
    protected string $severity = 'critical';

    protected ?string $name = 'Debug Mode Check';

    protected string $description = 'Checks if APP_DEBUG is enabled in production';

    public function scan(): array
    {
        if (! app()->environment('production')) {
            return $this->pass('Not in production environment');
        }

        $debugEnabled = config('app.debug', false);

        if ($debugEnabled) {
            return $this->suggest(
                'Debug mode is enabled - this exposes sensitive information in error pages',
                [
                    'recommendation' => 'Set APP_DEBUG=false in your production .env file',
                ]
            );
        }

        return $this->pass('Debug mode is disabled');
    }
}
