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
            ->hasCommand(BeaconCommand::class)
            ->hasCommand(\AtoAugustine\Beacon\Commands\MakeRuleCommand::class)
            ->hasCommand(\AtoAugustine\Beacon\Commands\ListRulesCommand::class);
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(RuleRegistry::class, function ($app) {
            return new RuleRegistry;
        });
    }

    public function packageBooted(): void
    {
        $this->registerDefaultRules();
    }

    protected function registerDefaultRules(): void
    {
        if (! config('beacon.enabled', true)) {
            return;
        }

        $registry = $this->app->make(RuleRegistry::class);
        $config = config('beacon', []);

        $defaultRules = [
            // Performance Rules
            \AtoAugustine\Beacon\Rules\Performance\ConfigCacheRule::class,
            \AtoAugustine\Beacon\Rules\Performance\RouteCacheRule::class,
            \AtoAugustine\Beacon\Rules\Performance\QueueSyncDriverRule::class,
            \AtoAugustine\Beacon\Rules\Performance\ViewCacheRule::class,
            \AtoAugustine\Beacon\Rules\Performance\EventCacheRule::class,
            \AtoAugustine\Beacon\Rules\Performance\NPlusOneQueriesRule::class,
            \AtoAugustine\Beacon\Rules\Performance\MissingChunkingRule::class,

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
            \AtoAugustine\Beacon\Rules\Architecture\DirectEnvUsageRule::class,
            \AtoAugustine\Beacon\Rules\Architecture\QueriesInBladeRule::class,
            \AtoAugustine\Beacon\Rules\Architecture\MissingMassAssignmentProtectionRule::class,
            \AtoAugustine\Beacon\Rules\Architecture\LogicInRoutesRule::class,
            \AtoAugustine\Beacon\Rules\Architecture\DirectInstantiationRule::class,
            \AtoAugustine\Beacon\Rules\Architecture\JsCssInBladeRule::class,
            \AtoAugustine\Beacon\Rules\Architecture\MagicStringsRule::class,
        ];

        // Register default rules
        foreach ($defaultRules as $ruleClass) {
            $rule = app($ruleClass);
            $ruleId = $rule->getId();

            // Check if rule is enabled in config
            $enabledRules = $config['enabled_rules'] ?? [];
            if (isset($enabledRules[$ruleId]) && $enabledRules[$ruleId] === false) {
                continue;
            }

            // Check minimum severity threshold
            $severityThreshold = $config['severity_threshold'] ?? 'low';
            if (! $this->meetsMinimumSeverity($rule->getSeverity(), $severityThreshold)) {
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

    protected function meetsMinimumSeverity(string $ruleSeverity, string $severityThreshold): bool
    {
        $severityLevels = [
            'low' => 1,
            'medium' => 2,
            'high' => 3,
            'critical' => 4,
        ];

        $ruleLevel = $severityLevels[$ruleSeverity] ?? 1;
        $thresholdLevel = $severityLevels[$severityThreshold] ?? 1;

        return $ruleLevel >= $thresholdLevel;
    }
}
