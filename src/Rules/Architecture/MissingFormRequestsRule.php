<?php

namespace AtoAugustine\Beacon\Rules\Architecture;

use AtoAugustine\Beacon\Rules\AbstractRule;
use Illuminate\Support\Facades\File;

class MissingFormRequestsRule extends AbstractRule
{
    public function getId(): string
    {
        return 'architecture.missing-form-requests';
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
        return 'Form Request Usage Check';
    }

    public function getDescription(): string
    {
        return 'Identifies controllers that may benefit from using Form Request classes for validation';
    }

    public function scan(): array
    {
        $controllersPath = app_path('Http/Controllers');
        $formRequestsPath = app_path('Http/Requests');

        if (! File::exists($controllersPath)) {
            return $this->pass('Controllers directory not found');
        }

        $controllers = File::allFiles($controllersPath);
        $controllersWithValidation = [];
        $hasFormRequests = File::exists($formRequestsPath) && count(File::files($formRequestsPath)) > 0;

        foreach ($controllers as $controller) {
            $content = File::get($controller->getPathname());

            // Check for validation patterns (Request::validate, $request->validate, Validator::make)
            $hasValidation = preg_match('/(\$request->validate|Request::validate|Validator::make)/i', $content);

            if ($hasValidation) {
                $controllersWithValidation[] = [
                    'file' => $controller->getRelativePathname(),
                ];
            }
        }

        if (! empty($controllersWithValidation) && ! $hasFormRequests) {
            return $this->suggest(
                'Found '.count($controllersWithValidation).' controller(s) with inline validation',
                [
                    'controllers' => $controllersWithValidation,
                    'recommendation' => 'Consider using Form Request classes (php artisan make:request) to separate validation logic from controllers',
                ]
            );
        }

        if (! empty($controllersWithValidation) && $hasFormRequests) {
            return $this->suggest(
                'Some controllers use inline validation - Form Requests are available and recommended',
                [
                    'controllers' => $controllersWithValidation,
                    'recommendation' => 'Move validation logic to Form Request classes for better organization and reusability',
                ]
            );
        }

        return $this->pass('Validation appears to be properly organized');
    }
}
