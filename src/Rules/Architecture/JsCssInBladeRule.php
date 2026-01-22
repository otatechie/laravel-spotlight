<?php

namespace Otatechie\Spotlight\Rules\Architecture;

use Illuminate\Support\Facades\File;
use Otatechie\Spotlight\Rules\AbstractRule;

class JsCssInBladeRule extends AbstractRule
{
    protected ?string $name = 'JS/CSS in Blade Check';

    protected string $description = 'Identifies JavaScript and CSS code in Blade templates that should be in separate files';

    public function scan(): array
    {
        $viewsPath = resource_path('views');

        if (! File::exists($viewsPath)) {
            return $this->pass('Views directory not found');
        }

        $views = File::allFiles($viewsPath);
        $viewsWithInlineCode = [];

        foreach ($views as $view) {
            $content = File::get($view->getPathname());
            $hasJs = false;
            $hasCss = false;

            // Check for <script> tags (excluding @push/@stack which are acceptable)
            if (preg_match('/<script[^>]*>(?!.*@push)(?!.*@stack).*<\/script>/is', $content)) {
                $hasJs = true;
            }

            // Check for inline JavaScript (excluding @push/@stack)
            if (preg_match('/onclick\s*=|onchange\s*=|onload\s*=/i', $content) &&
                ! preg_match('/@push|@stack/i', $content)) {
                $hasJs = true;
            }

            // Check for <style> tags (excluding @push/@stack)
            if (preg_match('/<style[^>]*>(?!.*@push)(?!.*@stack).*<\/style>/is', $content)) {
                $hasCss = true;
            }

            if ($hasJs || $hasCss) {
                $issues = [];
                if ($hasJs) {
                    $issues[] = 'JavaScript';
                }
                if ($hasCss) {
                    $issues[] = 'CSS';
                }

                $viewsWithInlineCode[] = [
                    'file' => $view->getRelativePathname(),
                    'issues' => $issues,
                ];
            }
        }

        if (! empty($viewsWithInlineCode)) {
            return $this->suggest(
                'Found '.count($viewsWithInlineCode).' Blade template(s) with inline JS/CSS',
                [
                    'views' => $viewsWithInlineCode,
                    'recommendation' => 'Move JavaScript and CSS to separate files. Use @push/@stack for including assets, or use Laravel Mix/Vite for asset compilation.',
                ]
            );
        }

        return $this->pass('No inline JavaScript or CSS found in Blade templates');
    }
}
