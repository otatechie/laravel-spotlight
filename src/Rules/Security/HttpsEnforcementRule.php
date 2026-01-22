<?php

namespace AtoAugustine\Beacon\Rules\Security;

use AtoAugustine\Beacon\Rules\AbstractRule;

class HttpsEnforcementRule extends AbstractRule
{
    public function getId(): string
    {
        return 'security.https-enforcement';
    }

    public function getCategory(): string
    {
        return 'security';
    }

    public function getSeverity(): string
    {
        return 'warning';
    }

    public function getName(): string
    {
        return 'HTTPS Enforcement Check';
    }

    public function getDescription(): string
    {
        return 'Checks if HTTPS is properly configured for production';
    }

    public function scan(): array
    {
        if (! app()->environment('production')) {
            return $this->pass('Not in production environment');
        }

        $urlScheme = parse_url(config('app.url', ''), PHP_URL_SCHEME);
        $forceHttps = config('app.force_https', false);

        if ($urlScheme !== 'https' && ! $forceHttps) {
            return $this->suggest(
                'HTTPS may not be enforced in production',
                [
                    'current_url' => config('app.url'),
                    'recommendation' => 'Set APP_URL to use https:// and consider enabling force_https in your AppServiceProvider',
                ]
            );
        }

        return $this->pass('HTTPS appears to be configured');
    }
}
