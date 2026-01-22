<?php

namespace Otatechie\Spotlight\Rules\Security;

use Otatechie\Spotlight\Rules\AbstractRule;

class HttpsEnforcementRule extends AbstractRule
{
    protected ?string $name = 'HTTPS Enforcement Check';

    protected string $description = 'Checks if HTTPS is properly configured for production';

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
