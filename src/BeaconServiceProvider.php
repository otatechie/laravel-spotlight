<?php

namespace AtoAugustine\Beacon;

use AtoAugustine\Beacon\Commands\BeaconCommand;
use AtoAugustine\Beacon\Rules\RuleRegistry;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class BeaconServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-beacon')
            ->hasConfigFile()
            ->hasCommand(BeaconCommand::class);
    }

    public function packageRegistered(): void
    {
        // Bind RuleRegistry as singleton
        $this->app->singleton(RuleRegistry::class, function ($app) {
            return new RuleRegistry();
        });
    }

    public function packageBooted(): void
    {
        // Register default rules
        $this->registerDefaultRules();
    }

    protected function registerDefaultRules(): void
    {
        $registry = $this->app->make(RuleRegistry::class);
        $config = config('beacon', []);

        $defaultRules = [
            // Performance Rules
            \AtoAugustine\Beacon\Rules\Performance\ConfigCacheRule::class,
            \AtoAugustine\Beacon\Rules\Performance\RouteCacheRule::class,
            \AtoAugustine\Beacon\Rules\Performance\QueueSyncDriverRule::class,
            \AtoAugustine\Beacon\Rules\Performance\ViewCacheRule::class,
            \AtoAugustine\Beacon\Rules\Performance\EventCacheRule::class,

            // Security Rules
            \AtoAugustine\Beacon\Rules\Security\AppDebugEnabledRule::class,
            \AtoAugustine\Beacon\Rules\Security\InsecureSessionDriverRule::class,
            \AtoAugustine\Beacon\Rules\Security\HttpsEnforcementRule::class,
            \AtoAugustine\Beacon\Rules\Security\CookieSecureFlagRule::class,

            // Architecture Rules
            \AtoAugustine\Beacon\Rules\Architecture\RouteClosureUsageRule::class,
            \AtoAugustine\Beacon\Rules\Architecture\LargeControllerRule::class,
            \AtoAugustine\Beacon\Rules\Architecture\MissingApiResourcesRule::class,
            \AtoAugustine\Beacon\Rules\Architecture\DirectDbQueriesRule::class,
            \AtoAugustine\Beacon\Rules\Architecture\MissingFormRequestsRule::class,
            \AtoAugustine\Beacon\Rules\Architecture\MissingServiceLayerRule::class,
        ];

        // Register default rules
        foreach ($defaultRules as $ruleClass) {
            $rule = app($ruleClass);
            $ruleId = $rule->getId();

            // Check if rule is enabled in config
            $enabledRules = $config['enabled_rules'] ?? [];
            if (isset($enabledRules[$ruleId]) && $enabledRules[$ruleId] === false) {
                continue; // Skip disabled rules
            }

            // Check minimum severity
            $minimumSeverity = $config['minimum_severity'] ?? 'info';
            if (! $this->meetsMinimumSeverity($rule->getSeverity(), $minimumSeverity)) {
                continue;
            }

            $registry->register($rule);
        }

        // Register custom rules
        $customRules = $config['custom_rules'] ?? [];
        foreach ($customRules as $customRuleClass) {
            if (class_exists($customRuleClass)) {
                $rule = app($customRuleClass);
                $registry->register($rule);
            }
        }
    }

    protected function meetsMinimumSeverity(string $ruleSeverity, string $minimumSeverity): bool
    {
        $severityLevels = [
            'info' => 1,
            'warning' => 2,
            'critical' => 3,
        ];

        $ruleLevel = $severityLevels[$ruleSeverity] ?? 1;
        $minimumLevel = $severityLevels[$minimumSeverity] ?? 1;

        return $ruleLevel >= $minimumLevel;
    }
}
