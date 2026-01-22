<?php

/**
 * Example Custom Rule for Laravel Spotlight
 *
 * This is a complete example showing how to create a custom rule.
 * Copy this file to your application and customize it for your needs.
 *
 * @see docs/CREATING_RULES.md for detailed documentation
 */

namespace App\Spotlight\Rules;

use Otatechie\Spotlight\Rules\AbstractRule;
use Illuminate\Support\Facades\File;

class ExampleCustomRule extends AbstractRule
{
    /**
     * Rule type - 'objective' for firm recommendations, 'advisory' for gentle suggestions
     * Defaults to 'advisory' if not set
     */
    protected string $type = 'advisory';

    /**
     * Severity level - 'info', 'warning', or 'critical'
     * Defaults to 'info' if not set
     */
    protected string $severity = 'warning';

    /**
     * Rule name - auto-generated from class name if not set
     * ExampleCustomRule -> "Example Custom"
     */
    protected ?string $name = 'Example Custom Rule';

    /**
     * Rule description - always set this!
     */
    protected string $description = 'Example rule that demonstrates how to create custom rules';

    // id: auto-generated as 'custom.example-custom' (from namespace + class name)
    // category: auto-detected as 'custom' from namespace

    /**
     * Main scanning logic
     * This method is called when the rule is executed
     *
     * @return array<string, mixed>
     */
    public function scan(): array
    {
        // Example: Check if a specific file exists
        $requiredFile = base_path('custom-config.php');

        if (! File::exists($requiredFile)) {
            // Use suggest() when an issue is detected (friendly, non-judgmental)
            return $this->suggest(
                'Required configuration file is missing',
                [
                    'file' => $requiredFile,
                    'recommendation' => 'Create the custom-config.php file in your project root',
                    'example_content' => '<?php return [];',
                ]
            );
        }

        // Use pass() when everything is okay
        return $this->pass('Required configuration file exists');
    }
}

/**
 * To use this rule:
 *
 * 1. Copy this file to app/Spotlight/Rules/ExampleCustomRule.php
 * 2. Update the namespace to match your application structure
 * 3. Customize the scanning logic in the scan() method
 * 4. Register it in config/spotlight.php:
 *
 *    return [
 *        'custom_rules' => [
 *            \App\Spotlight\Rules\ExampleCustomRule::class,
 *        ],
 *    ];
 *
 * 5. Run: php artisan spotlight:scan
 */
