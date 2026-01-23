<?php

namespace Otatechie\Spotlight;

use Otatechie\Spotlight\Commands\SpotlightCommand;
use Otatechie\Spotlight\Rules\RuleRegistry;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class SpotlightServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-spotlight')
            ->hasConfigFile('spotlight')
            ->hasCommand(SpotlightCommand::class)
            ->hasCommand(\Otatechie\Spotlight\Commands\MakeRuleCommand::class)
            ->hasCommand(\Otatechie\Spotlight\Commands\ListRulesCommand::class);
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

    public function boot(): void
    {
        parent::boot();

        // Override the publish tag to use 'spotlight-config' instead of the default
        // This runs after hasConfigFile() registers it, so we override the tag
        $configPath = $this->package->basePath('/../config/spotlight.php');
        if (file_exists($configPath)) {
            $this->publishes([
                $configPath => config_path('spotlight.php'),
            ], 'spotlight-config');
        }
    }

    protected function registerDefaultRules(): void
    {
        if (! config('spotlight.enabled', true)) {
            return;
        }

        $registry = $this->app->make(RuleRegistry::class);
        $config = config('spotlight', []);

        $defaultRules = [
            // Performance Rules
            \Otatechie\Spotlight\Rules\Performance\ConfigCacheRule::class,
            \Otatechie\Spotlight\Rules\Performance\RouteCacheRule::class,
            \Otatechie\Spotlight\Rules\Performance\QueueSyncDriverRule::class,
            \Otatechie\Spotlight\Rules\Performance\ViewCacheRule::class,
            \Otatechie\Spotlight\Rules\Performance\EventCacheRule::class,
            \Otatechie\Spotlight\Rules\Performance\NPlusOneQueriesRule::class,
            \Otatechie\Spotlight\Rules\Performance\MissingChunkingRule::class,

            // Security Rules
            \Otatechie\Spotlight\Rules\Security\AppDebugEnabledRule::class,
            \Otatechie\Spotlight\Rules\Security\InsecureSessionDriverRule::class,
            \Otatechie\Spotlight\Rules\Security\HttpsEnforcementRule::class,
            \Otatechie\Spotlight\Rules\Security\CookieSecureFlagRule::class,

            // Architecture Rules
            \Otatechie\Spotlight\Rules\Architecture\RouteClosureUsageRule::class,
            \Otatechie\Spotlight\Rules\Architecture\LargeControllerRule::class,
            \Otatechie\Spotlight\Rules\Architecture\MissingApiResourcesRule::class,
            \Otatechie\Spotlight\Rules\Architecture\MissingFormRequestsRule::class,
            \Otatechie\Spotlight\Rules\Architecture\DirectEnvUsageRule::class,
            \Otatechie\Spotlight\Rules\Architecture\QueriesInBladeRule::class,
            \Otatechie\Spotlight\Rules\Architecture\MissingMassAssignmentProtectionRule::class,
            \Otatechie\Spotlight\Rules\Architecture\LogicInRoutesRule::class,
            \Otatechie\Spotlight\Rules\Architecture\JsCssInBladeRule::class,
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
