<?php

namespace AtoAugustine\Beacon\Rules\Architecture;

use AtoAugustine\Beacon\Rules\AbstractRule;
use Illuminate\Support\Facades\File;

class MissingApiResourcesRule extends AbstractRule
{
    public function getId(): string
    {
        return 'architecture.missing-api-resources';
    }

    public function getCategory(): string
    {
        return 'architecture';
    }

    public function getSeverity(): string
    {
        return 'info';
    }

    public function getName(): string
    {
        return 'API Resources Check';
    }

    public function getDescription(): string
    {
        return 'Identifies API routes that may benefit from using API resources';
    }

    public function scan(): array
    {
        $apiRoutesPath = base_path('routes/api.php');
        
        if (! File::exists($apiRoutesPath)) {
            return $this->pass('API routes file not found');
        }

        $resourcesPath = app_path('Http/Resources');
        $hasResources = File::exists($resourcesPath) && count(File::files($resourcesPath)) > 0;

        $content = File::get($apiRoutesPath);
        $hasApiRoutes = preg_match('/Route::(get|post|put|patch|delete)/i', $content);

        if ($hasApiRoutes && ! $hasResources) {
            return $this->suggest(
                'API routes found but no API resources detected',
                [
                    'recommendation' => 'Consider using API resources (php artisan make:resource) to standardize your API responses',
                ]
            );
        }

        return $this->pass('API resources are being used or no API routes found');
    }
}
