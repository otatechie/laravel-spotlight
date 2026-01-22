# Laravel Beacon

A lighthouse-style diagnostics tool that scans Laravel applications for performance, security, and architecture issues. Built with a modular rule system that makes it easy to extend and customize.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/otatechie/laravel-beacon.svg?style=flat-square)](https://packagist.org/packages/otatechie/laravel-beacon)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/otatechie/laravel-beacon/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/otatechie/laravel-beacon/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/otatechie/laravel-beacon/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/otatechie/laravel-beacon/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/otatechie/laravel-beacon.svg?style=flat-square)](https://packagist.org/packages/otatechie/laravel-beacon)

## What is Beacon?

**Beacon is NOT:**
- ❌ **Code formatter** - Beacon doesn't format or fix your code style
- ❌ **Linter** - Beacon doesn't enforce coding standards or syntax rules
- ❌ **Debugger** - Beacon doesn't debug runtime errors or exceptions

**Beacon IS:**
- ✅ **Diagnostic Scanner** - Identifies potential issues before they become problems
- ✅ **Best Practices Advisor** - Suggests improvements based on Laravel best practices
- ✅ **Performance Analyzer** - Detects performance bottlenecks and optimization opportunities
- ✅ **Security Auditor** - Flags security vulnerabilities and misconfigurations
- ✅ **Architecture Mentor** - Provides gentle guidance on code organization and structure
- ✅ **Guidance Tool** - Offers suggestions, not enforcement (you're in control)

Laravel Beacon helps you identify and fix issues in your Laravel application before they become problems. It scans your application for:

- **Performance Issues**: Missing caches, inefficient queue drivers, N+1 queries
- **Security Vulnerabilities**: Debug mode in production, insecure configurations
- **Architecture Problems**: Fat controllers, route closures, code organization

## Philosophy

**Beacon provides guidance, not enforcement. No shaming, no judgment.**

Laravel Beacon is designed as a **friendly mentor**, not an angry linter. We believe in helping developers improve their code through gentle suggestions, not through shame or rigid enforcement.

### Core Principles

- **No Shaming** - We use "could improve" and "consider" instead of "MUST fix" or "WRONG"
- **No Judgment** - We inform about potential improvements without making you feel bad
- **User Control** - Every rule can be disabled if it doesn't fit your project
- **Objective vs Advisory** - Security and performance issues are flagged firmly but respectfully; architecture suggestions are gentle recommendations
- **Fast & Lightweight** - Beacon runs quickly (< 1 second) using lightweight checks, config inspection, and Laravel APIs

### Language Examples

**Beacon says:**
- ✅ "Config cache could improve performance"
- ✅ "Consider extracting business logic to service classes"
- ✅ "This works for development, but may need review for production"

**Beacon never says:**
- ❌ "Config cache is missing - FIX IT!"
- ❌ "Your controller is too large - this is wrong!"
- ❌ "You must use Form Requests!"

Our goal is to help you build better Laravel applications through helpful guidance, not rigid enforcement or shame.

## Features

- 🔍 **Comprehensive Scanning**: Automatically scans your Laravel application for common issues
- 🧩 **Modular Rule System**: Easy to extend with custom rules (just 2-3 properties needed!)
- ⚙️ **Full User Control**: Enable/disable any rule that doesn't fit your project
- 🎯 **Objective vs Advisory**: Distinguishes firm recommendations from gentle suggestions
- ⚡ **Lightning Fast**: Executes in < 1 second using lightweight checks
- 🛡️ **Error Handling**: Robust error handling prevents single rule failures from crashing scans
- 📊 **Multiple Output Formats**: Table and JSON output formats
- 🎨 **Clean API**: Convention-based auto-detection eliminates boilerplate
- 🛠️ **Developer Tools**: Rule generator, rule listing, CI/CD exit codes

## What Makes Beacon Different?

Unlike other diagnostic tools that are aggressive and dogmatic, Beacon is designed as a **friendly mentor**:

- ✅ **Informs, doesn't judge** - Suggests improvements without shaming
- ✅ **User control** - Every rule can be disabled if it doesn't apply
- ✅ **Fast execution** - Runs in < 1 second, not 10-30 seconds
- ✅ **Easy to extend** - Create custom rules with minimal code
- ✅ **Non-judgmental** - Uses "consider" and "could improve" instead of "MUST fix"

See [Why Beacon?](docs/WHY_BEACON.md) for a detailed comparison with other tools.


## Installation

You can install the package via composer:

```bash
composer require otatechie/laravel-beacon
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="laravel-beacon-config"
```

The config file includes options for:

- Enabling/disabling specific rules
- Registering custom rules
- Setting minimum severity levels
- Configuring error handling
- Enabling debug mode

See the [configuration documentation](docs/EXAMPLES.md#configuration-examples) for details.

## Usage

### Basic Scanning

Run a full scan of your application:

```bash
php artisan beacon:scan
```

### Scan Specific Categories

Scan only performance issues:

```bash
php artisan beacon:scan --category=performance
```

Scan multiple categories:

```bash
php artisan beacon:scan --category=performance --category=security
```

### Get JSON Output

```bash
php artisan beacon:scan --format=json
```

### Filter by Severity

Only show critical issues:

```bash
php artisan beacon:scan --severity=critical
```

### Exit Codes for CI/CD

Beacon uses a sophisticated exit code system for CI/CD integration:

**Exit Code Mapping:**
- `0` - No issues found (clean scan)
- `1` - Only low severity issues found
- `2` - Medium or high severity issues found
- `3` - Critical issues found

**Usage Examples:**

```bash
# Default: Auto-detect exit code based on severity
php artisan beacon:scan

# Fail only on critical issues
php artisan beacon:scan --fail-on=critical

# Fail on high or critical issues
php artisan beacon:scan --fail-on=high

# Fail on any issues (including low)
php artisan beacon:scan --fail-on=low
```

**GitHub Actions Example:**

```yaml
- name: Run Beacon Scan
  run: php artisan beacon:scan || exit 1
```

### List Available Rules

See all available rules:

```bash
php artisan beacon:rules
```

Get details about a specific rule:

```bash
php artisan beacon:rules --rule=performance.config-cache
```

### Create Custom Rules

Generate a new rule class:

```bash
php artisan beacon:make-rule MyCustomRule --category=performance --type=objective
```

This creates a rule class with proper structure and auto-registers it.

### Programmatic Usage

```php
use AtoAugustine\Beacon\Beacon;

$beacon = app(Beacon::class);
$results = $beacon->scan(['performance', 'security']);

// Access results
$summary = $results['summary'];
$categories = $results['categories'];
```

## Rule Types & Severity

Beacon uses a two-dimensional classification system:

### Rule Types

- **Objective Rules** (`type: 'objective'`) - Firm recommendations based on hard facts
  - Examples: Debug enabled, config not cached, insecure cookies
  - Beacon can be firm with these
  
- **Advisory Rules** (`type: 'advisory'`) - Gentle suggestions based on best practices
  - Examples: Fat controllers, route closures, folder structure
  - Beacon should be gentle with these

### Severity Levels

Beacon uses 4 severity levels with numeric weights for scoring:

| Level | Weight | Purpose |
|-------|--------|---------|
| `critical` | 100 | Production-breaking / security risk |
| `high` | 70 | Serious performance or stability issue |
| `medium` | 40 | Degraded behavior / suboptimal config |
| `low` | 10 | Minor improvement / suggestion |

**Health Score:** Beacon calculates a health score (0-100%) based on severity weights, giving you a quick overview of your application's status.

All rules can be disabled in `config/beacon.php` if they don't apply to your project.

## Built-in Rules

### Performance Rules

- **Config Cache**: Checks if config cache is enabled in production
- **Route Cache**: Checks if route cache is enabled in production
- **Queue Sync Driver**: Warns about sync queue driver in production
- **View Cache**: Checks if view cache is enabled
- **Event Cache**: Checks if event cache is enabled
- **N+1 Queries**: Detects potential N+1 query problems
- **Missing Chunking**: Identifies large dataset operations without chunking

### Security Rules

- **App Debug Enabled**: Critical check for debug mode in production
- **Insecure Session Driver**: Warns about insecure session drivers
- **HTTPS Enforcement**: Checks if HTTPS is properly enforced
- **Cookie Secure Flag**: Verifies secure cookie configuration

### Architecture Rules

- **Route Closure Usage**: Detects route closures preventing caching
- **Large Controller Detection**: Identifies controllers exceeding 300 lines
- **Direct ENV Usage**: Finds direct `env()` calls that should use `config()`
- **Queries in Blade**: Detects database queries in Blade templates
- **Mass Assignment Protection**: Checks for missing `$fillable` or `$guarded`
- **Logic in Routes**: Identifies complex logic in route files
- **Direct Instantiation**: Detects `new Class()` usage instead of dependency injection
- **JS/CSS in Blade**: Finds inline JavaScript and CSS in templates
- **Magic Strings**: Identifies hardcoded strings that should be constants

### Rule Sources

Beacon's rules are based on authoritative sources and community best practices:

- **[Laravel Official Documentation](https://laravel.com/docs)** - Official Laravel best practices and recommendations
- **[Laravel Best Practices Guide](https://github.com/alexeymezenin/laravel-best-practices)** - Community-maintained best practices (12.2k+ stars)
- **Laravel Community Standards** - Widely accepted patterns from the Laravel community
- **Security Best Practices** - Common security recommendations for Laravel applications

For more details on the sources and rationale behind each rule, see [docs/LARAVEL_BEST_PRACTICES.md](docs/LARAVEL_BEST_PRACTICES.md).

## Creating Custom Rules

Laravel Beacon makes it easy to create your own custom rules. See the [Creating Rules Guide](docs/CREATING_RULES.md) for detailed instructions.

### Quick Example

```php
<?php

namespace App\Beacon\Rules;

use AtoAugustine\Beacon\Rules\AbstractRule;

class MyCustomRule extends AbstractRule
{
    public function getId(): string
    {
        return 'custom.my-rule';
    }

    public function getCategory(): string
    {
        return 'custom';
    }

    public function getSeverity(): string
    {
        return 'medium';
    }

    public function getName(): string
    {
        return 'My Custom Rule';
    }

    public function getDescription(): string
    {
        return 'Checks for a specific issue';
    }

    public function scan(): array
    {
        if ($issueFound) {
            return $this->suggest(
                'Issue description',
                ['recommendation' => 'How to fix it']
            );
        }

        return $this->pass('Everything looks good!');
    }
}
```

Register it in `config/beacon.php`:

```php
return [
    'custom_rules' => [
        \App\Beacon\Rules\MyCustomRule::class,
    ],
];
```

## Configuration

Publish the config file:

```bash
php artisan vendor:publish --tag="laravel-beacon-config"
```

Key configuration options:

- `enabled_rules` - Enable/disable specific rules
- `custom_rules` - Register your own custom rules
- `minimum_severity` - Filter rules by severity level
- `debug` - Enable debug logging
- `error_handling` - Control error handling behavior

See [Examples & Configuration](docs/EXAMPLES.md) for more details.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Documentation

- [Creating Custom Rules](docs/CREATING_RULES.md) - Guide to creating your own rules
- [Examples & Configuration](docs/EXAMPLES.md) - Usage examples and configuration options

## Contributing

Contributions are welcome! Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Ato Augustine](https://github.com/otatechie)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Laravel Trademark Disclaimer

Laravel is a trademark of Taylor Otwell. This package is not officially associated with Laravel or Taylor Otwell. The "laravel-" prefix in the package name is used to indicate compatibility with the Laravel framework and follows community naming conventions.
