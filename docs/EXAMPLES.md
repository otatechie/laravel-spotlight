# Laravel Beacon Examples

This document provides practical examples and advanced usage patterns for Laravel Beacon.

> **Basic usage examples are in the [README](../README.md#usage).** This document focuses on advanced configuration and integration patterns.

## Configuration Examples

### Disable Specific Rules

Edit `config/beacon.php`:

```php
return [
    'enabled_rules' => [
        'performance.config-cache' => false, // Disable config cache check
        'architecture.fat-controller' => false, // Disable fat controller check
    ],
];
```

### Add Custom Rules

Edit `config/beacon.php`:

```php
return [
    'custom_rules' => [
        \App\Beacon\Rules\MyCustomRule::class,
        \App\Beacon\Rules\AnotherRule::class,
    ],
];
```

### Set Severity Threshold

Only run rules with a certain severity level or higher:

```php
return [
    'severity_threshold' => 'medium', // Only run medium, high, and critical rules
];
```

### Enable Debug Mode

Get detailed logging of rule execution:

```php
return [
    'debug' => true,
];
```

Or via environment variable:

```env
BEACON_DEBUG=true
```

## Programmatic Usage

### Using Beacon in Code

```php
use AtoAugustine\Beacon\Beacon;
use AtoAugustine\Beacon\Rules\RuleRegistry;

// Get Beacon instance
$beacon = app(Beacon::class);

// Run all scans
$results = $beacon->scan();

// Scan specific categories
$results = $beacon->scan(['performance', 'security']);

// Access results
$summary = $results['summary'];
$categories = $results['categories'];
$rules = $results['rules'];
```

### Register Rules Programmatically

```php
use AtoAugustine\Beacon\Rules\RuleRegistry;

$registry = app(RuleRegistry::class);

// Register a single rule
$registry->register(\App\Beacon\Rules\MyRule::class);

// Register multiple rules
$registry->registerMany([
    \App\Beacon\Rules\Rule1::class,
    \App\Beacon\Rules\Rule2::class,
]);

// Get rules by category
$performanceRules = $registry->byCategory('performance');

// Get rules by severity
$criticalRules = $registry->bySeverity('critical');
```

## CI/CD Integration

### GitHub Actions Example

```yaml
name: Beacon Scan

on: [push, pull_request]

jobs:
  scan:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
      
      - name: Install Dependencies
        run: composer install
      
      - name: Run Beacon Scan
        run: php artisan beacon:scan --format=json > beacon-results.json
      
      - name: Upload Results
        uses: actions/upload-artifact@v3
        with:
          name: beacon-results
          path: beacon-results.json
```

### GitLab CI Example

```yaml
beacon-scan:
  stage: test
  script:
    - composer install
    - php artisan beacon:scan --format=json > beacon-results.json
  artifacts:
    paths:
      - beacon-results.json
    expire_in: 1 week
```

## Custom Rule Example

Here's a complete example using Beacon's clean auto-detection approach:

```php
<?php

namespace App\Beacon\Rules\Performance; // Category auto-detected!

use AtoAugustine\Beacon\Rules\AbstractRule;
use Illuminate\Support\Facades\DB;

class DatabaseConnectionRule extends AbstractRule
{
    // Only define what's different - everything else is auto-detected!
    protected string $severity = 'high';
    protected string $description = 'Verifies database connection is working';
    
    // id: auto-generated as 'performance.database-connection'
    // category: auto-detected as 'performance' from namespace
    // name: auto-generated as 'Database Connection' from class name

    public function scan(): array
    {
        try {
            DB::connection()->getPdo();
            
            return $this->pass('Database connection is working');
        } catch (\Exception $e) {
            return $this->suggest(
                'Database connection failed: ' . $e->getMessage(),
                [
                    'recommendation' => 'Check your database configuration in .env',
                    'exception' => get_class($e),
                ]
            );
        }
    }
}
```

Register it in `config/beacon.php`:

```php
return [
    'custom_rules' => [
        \App\Beacon\Rules\Performance\DatabaseConnectionRule::class,
    ],
];
```

> See [CREATING_RULES.md](CREATING_RULES.md) for detailed rule creation guide.

## Advanced Configuration

### Error Handling

Control how errors are handled:

```php
return [
    'error_handling' => 'continue', // Continue scanning when a rule fails
    // or
    'error_handling' => 'stop', // Stop scanning when a rule throws an exception
];
```

### Custom Categories

Define your own categories:

```php
return [
    'categories' => [
        'performance' => 'Performance',
        'security' => 'Security',
        'architecture' => 'Architecture',
        'custom' => 'Custom Checks',
        'compliance' => 'Compliance',
    ],
];
```

