<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Enable Beacon
    |--------------------------------------------------------------------------
    |
    | Set to false to completely disable Beacon scanning.
    |
    */
    'enabled' => true,

    /*
    |--------------------------------------------------------------------------
    | Enabled Rules
    |--------------------------------------------------------------------------
    |
    | Specify which rules should be enabled. Set to false to disable a rule.
    | You can disable rules by their ID. This gives you full control over
    | which checks run in your project.
    |
    | Example: Disable a rule that doesn't fit your project
    | 'architecture.large-controller' => false,
    |
    */
    'enabled_rules' => [
        // Performance Rules
        'performance.config-cache' => true,
        'performance.route-cache' => true,
        'performance.queue-sync-driver' => true,
        'performance.view-cache' => true,
        'performance.event-cache' => true,
        'performance.n-plus-one-queries' => true,
        'performance.missing-chunking' => true,

        // Security Rules
        'security.app-debug-enabled' => true,
        'security.session-driver' => true,
        'security.https-enforcement' => true,
        'security.cookie-secure-flag' => true,

        // Architecture Rules
        'architecture.route-closure-usage' => true,
        'architecture.large-controller' => true,
        'architecture.missing-api-resources' => true,
        'architecture.direct-db-queries' => true,
        'architecture.missing-form-requests' => true,
        'architecture.missing-service-layer' => true,
        'architecture.direct-env-usage' => true,
        'architecture.queries-in-blade' => true,
        'architecture.missing-mass-assignment-protection' => true,
        'architecture.logic-in-routes' => true,
        'architecture.direct-instantiation' => true,
        'architecture.js-css-in-blade' => true,
        'architecture.magic-strings' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Rules
    |--------------------------------------------------------------------------
    |
    | Register your own custom rules here. Rules must implement
    | Otatechie\Beacon\Rules\RuleInterface
    |
    */
    'custom_rules' => [
        // Example:
        // \App\Beacon\Rules\MyCustomRule::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Severity Settings
    |--------------------------------------------------------------------------
    |
    | Filter rules by minimum severity level. Rules below this level
    | will be skipped during scanning.
    |
    | Options: 'low', 'medium', 'high', 'critical'
    |
    */
    'severity_threshold' => 'low',

    /*
    |--------------------------------------------------------------------------
    | Debug Mode
    |--------------------------------------------------------------------------
    |
    | Enable debug logging for rule execution. When enabled, detailed
    | information about rule execution will be logged.
    |
    */
    'debug' => env('BEACON_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | CI Behavior
    |--------------------------------------------------------------------------
    |
    | Configure exit code behavior for CI/CD pipelines.
    |
    | Options: 'critical', 'high', 'medium', 'low', 'auto'
    | - 'auto' uses default exit codes (0=clean, 1=low, 2=medium/high, 3=critical)
    | - Other options will exit with error code if that severity or higher is found
    |
    */
    'fail_on' => 'auto',

    /*
    |--------------------------------------------------------------------------
    | Error Handling
    |--------------------------------------------------------------------------
    |
    | Configure how errors are handled during rule execution.
    |
    | 'continue' - Continue scanning other rules when one fails
    | 'stop' - Stop scanning when a rule throws an exception
    |
    */
    'error_handling' => 'continue',

    /*
    |--------------------------------------------------------------------------
    | Output Settings
    |--------------------------------------------------------------------------
    |
    | Configure CLI output behavior.
    |
    */
    'output' => [
        'show_suggestions' => true,
        'show_tips' => true,
        'color' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Categories
    |--------------------------------------------------------------------------
    |
    | Define available categories for rules. Rules can be grouped
    | by these categories.
    |
    */
    'categories' => [
        'performance' => 'Performance',
        'security' => 'Security',
        'architecture' => 'Architecture',
    ],
];
