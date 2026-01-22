<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Enabled Rules
    |--------------------------------------------------------------------------
    |
    | Specify which rules should be enabled. Set to false to disable a rule.
    | You can disable rules by their ID or by category.
    |
    */
    'enabled_rules' => [
        // Performance Rules
        'performance.config-cache' => true,
        'performance.route-cache' => true,
        'performance.queue-sync-driver' => true,
        'performance.view-cache' => true,
        'performance.event-cache' => true,

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
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Rules
    |--------------------------------------------------------------------------
    |
    | Register your own custom rules here. Rules must implement
    | AtoAugustine\Beacon\Rules\RuleInterface
    |
    */
    'custom_rules' => [
        // Example:
        // \App\Beacon\Rules\MyCustomRule::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Severity Filtering
    |--------------------------------------------------------------------------
    |
    | Filter rules by minimum severity level. Rules below this level
    | will be skipped during scanning.
    |
    | Options: 'info', 'warning', 'critical'
    |
    */
    'minimum_severity' => 'info',

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
