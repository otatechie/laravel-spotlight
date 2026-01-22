<?php

namespace AtoAugustine\Beacon\Rules\Security;

use AtoAugustine\Beacon\Rules\AbstractRule;

class CookieSecureFlagRule extends AbstractRule
{
    public function getId(): string
    {
        return 'security.cookie-secure-flag';
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
        return 'Cookie Secure Flag Check';
    }

    public function getDescription(): string
    {
        return 'Checks if secure cookie flag is enabled for production';
    }

    public function scan(): array
    {
        if (! app()->environment('production')) {
            return $this->pass('Not in production environment');
        }

        $secure = config('session.secure', false);
        $httpOnly = config('session.http_only', true);

        if (! $secure) {
            return $this->suggest(
                'Secure cookie flag is not enabled',
                [
                    'recommendation' => 'Set SESSION_SECURE_COOKIE=true in production to ensure cookies are only sent over HTTPS',
                ]
            );
        }

        if (! $httpOnly) {
            return $this->suggest(
                'HttpOnly cookie flag is not enabled',
                [
                    'recommendation' => 'Set SESSION_HTTP_ONLY=true to prevent JavaScript access to session cookies',
                ]
            );
        }

        return $this->pass('Cookie security flags are properly configured');
    }
}
