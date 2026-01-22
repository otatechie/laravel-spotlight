<?php

namespace Otatechie\Spotlight\Rules\Architecture;

use Otatechie\Spotlight\Rules\AbstractRule;
use Illuminate\Support\Facades\File;

class MissingMassAssignmentProtectionRule extends AbstractRule
{
    protected ?string $name = 'Mass Assignment Protection Check';

    protected string $description = 'Identifies models that may be missing mass assignment protection';

    public function scan(): array
    {
        $modelsPath = app_path('Models');

        if (! File::exists($modelsPath)) {
            return $this->pass('Models directory not found');
        }

        $models = File::allFiles($modelsPath);
        $modelsWithoutProtection = [];

        foreach ($models as $model) {
            $content = File::get($model->getPathname());

            // Check if model extends Model (Eloquent)
            if (! preg_match('/extends\s+Model/i', $content)) {
                continue;
            }

            // Check for mass assignment protection
            $hasFillable = str_contains($content, '$fillable') || str_contains($content, 'protected $fillable');
            $hasGuarded = str_contains($content, '$guarded') || str_contains($content, 'protected $guarded');

            if (! $hasFillable && ! $hasGuarded) {
                $modelsWithoutProtection[] = [
                    'file' => $model->getRelativePathname(),
                ];
            }
        }

        if (! empty($modelsWithoutProtection)) {
            return $this->suggest(
                'Found '.count($modelsWithoutProtection).' model(s) without mass assignment protection',
                [
                    'models' => $modelsWithoutProtection,
                    'recommendation' => 'Add $fillable or $guarded property to protect against mass assignment vulnerabilities. Prefer $fillable for explicit allow-list.',
                ]
            );
        }

        return $this->pass('All models appear to have mass assignment protection');
    }
}
