# Laravel Spotlight

A lighthouse-style diagnostics tool that scans Laravel applications for performance, security, and architecture issues. Built with a modular rule system that makes it easy to extend and customize.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/otatechie/laravel-spotlight.svg?style=flat-square)](https://packagist.org/packages/otatechie/laravel-spotlight)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/otatechie/laravel-spotlight/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/otatechie/laravel-spotlight/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/otatechie/laravel-spotlight/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/otatechie/laravel-spotlight/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/otatechie/laravel-spotlight.svg?style=flat-square)](https://packagist.org/packages/otatechie/laravel-spotlight)

## What is Spotlight?

**Spotlight is NOT:**
- ❌ **Code formatter** - Spotlight doesn't format or fix your code style
- ❌ **Linter** - Spotlight doesn't enforce coding standards or syntax rules
- ❌ **Debugger** - Spotlight doesn't debug runtime errors or exceptions

**Spotlight IS:**
- ✅ **Diagnostic Scanner** - Identifies potential issues before they become problems
- ✅ **Best Practices Advisor** - Suggests improvements based on Laravel best practices
- ✅ **Performance Analyzer** - Detects performance bottlenecks and optimization opportunities
- ✅ **Security Auditor** - Flags security vulnerabilities and misconfigurations
- ✅ **Architecture Mentor** - Provides gentle guidance on code organization and structure
- ✅ **Guidance Tool** - Offers suggestions, not enforcement (you're in control)

Laravel Spotlight helps you identify and fix issues in your Laravel application before they become problems. It scans your application for:

- **Performance Issues**: Missing caches, inefficient queue drivers, N+1 queries
- **Security Vulnerabilities**: Debug mode in production, insecure configurations
- **Architecture Problems**: Fat controllers, route closures, code organization

## Philosophy

**Spotlight provides guidance, not enforcement. No shaming, no judgment.**

Laravel Spotlight is designed as a **friendly mentor**, not an angry linter. We believe in helping developers improve their code through gentle suggestions, not through shame or rigid enforcement.

- **No Shaming** - We use "could improve" and "consider" instead of "MUST fix" or "WRONG"
- **User Control** - Every rule can be disabled if it doesn't fit your project
- **Objective vs Advisory** - Security/performance issues are flagged firmly; architecture suggestions are gentle
- **Fast & Lightweight** - Executes in < 1 second using lightweight checks

See [Why Spotlight?](docs/WHY_SPOTLIGHT.md) for a detailed comparison with other tools.

## Features

- 🔍 **Comprehensive Scanning** - 25+ built-in rules covering performance, security, and architecture
- 🧩 **Modular Rule System** - Easy to extend with custom rules (auto-detection eliminates boilerplate)
- ⚙️ **Full User Control** - Enable/disable any rule that doesn't fit your project
- 🎯 **Severity Scoring** - 4-level severity system with health score calculation
- ⚡ **Lightning Fast** - Executes in < 1 second
- 🛡️ **Error Handling** - Robust error handling prevents single rule failures from crashing scans
- 📊 **Multiple Output Formats** - Table and JSON output formats
- 🛠️ **Developer Tools** - Rule generator, rule listing, CI/CD exit codes


## Installation

You can install the package via composer:

```bash
composer require otatechie/laravel-spotlight
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="spotlight-config"
```

The config file includes options for:

- Enabling/disabling specific rules
- Registering custom rules
- Setting severity threshold
- Configuring error handling
- Enabling debug mode

See the [configuration documentation](docs/EXAMPLES.md#configuration-examples) for details.

## Usage

### Basic Scanning

Run a full scan of your application:

```bash
php artisan spotlight:scan
```

### Scan Specific Categories

Scan only performance issues:

```bash
php artisan spotlight:scan --category=performance
```

Scan multiple categories:

```bash
php artisan spotlight:scan --category=performance --category=security
```

### Get JSON Output

```bash
php artisan spotlight:scan --format=json
```

### Filter by Severity

Only show critical issues:

```bash
php artisan spotlight:scan --severity=critical
```

### Exit Codes for CI/CD

Spotlight uses a sophisticated exit code system for CI/CD integration:

**Exit Code Mapping:**
- `0` - No issues found (clean scan)
- `1` - Only low severity issues found
- `2` - Medium or high severity issues found
- `3` - Critical issues found

**Usage Examples:**

```bash
# Default: Auto-detect exit code based on severity
php artisan spotlight:scan

# Fail only on critical issues
php artisan spotlight:scan --fail-on=critical

# Fail on high or critical issues
php artisan spotlight:scan --fail-on=high

# Fail on any issues (including low)
php artisan spotlight:scan --fail-on=low
```

**GitHub Actions Example:**

```yaml
- name: Run Spotlight Scan
  run: php artisan spotlight:scan || exit 1
```

### List Available Rules

See all available rules:

```bash
php artisan spotlight:rules
```

Get details about a specific rule:

```bash
php artisan spotlight:rules --rule=performance.config-cache
```

### Create Custom Rules

Generate a new rule class:

```bash
php artisan spotlight:make-rule MyCustomRule --category=performance --type=objective
```

This creates a rule class with proper structure and auto-registers it.

### Programmatic Usage

```php
use Otatechie\Spotlight\Spotlight;

$spotlight = app(Spotlight::class);
$results = $spotlight->scan(['performance', 'security']);

// Access results
$summary = $results['summary'];
$categories = $results['categories'];
```

## Rule Types & Severity

Spotlight uses a two-dimensional classification system:

### Rule Types

- **Objective Rules** (`type: 'objective'`) - Firm recommendations based on hard facts
  - Examples: Debug enabled, config not cached, insecure cookies
  - Spotlight can be firm with these
  
- **Advisory Rules** (`type: 'advisory'`) - Gentle suggestions based on best practices
  - Examples: Fat controllers, route closures, folder structure
  - Spotlight should be gentle with these

### Severity Levels

Spotlight uses 4 severity levels with numeric weights for scoring:

| Level | Weight | Purpose |
|-------|--------|---------|
| `critical` | 100 | Production-breaking / security risk |
| `high` | 70 | Serious performance or stability issue |
| `medium` | 40 | Degraded behavior / suboptimal config |
| `low` | 10 | Minor improvement / suggestion |

**Health Score:** Spotlight calculates a health score (0-100%) based on severity weights, giving you a quick overview of your application's status.

All rules can be disabled in `config/spotlight.php` if they don't apply to your project.

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
- **Missing API Resources**: Detects API routes without API resources
- **Direct DB Queries**: Identifies direct database queries in controllers
- **Missing Form Requests**: Detects controllers using inline validation instead of Form Requests
- **Missing Service Layer**: Identifies controllers with business logic that should be in services
- **Direct ENV Usage**: Finds direct `env()` calls that should use `config()`
- **Queries in Blade**: Detects database queries in Blade templates
- **Mass Assignment Protection**: Checks for missing `$fillable` or `$guarded`
- **Logic in Routes**: Identifies complex logic in route files
- **Direct Instantiation**: Detects `new Class()` usage instead of dependency injection
- **JS/CSS in Blade**: Finds inline JavaScript and CSS in templates
- **Magic Strings**: Identifies hardcoded strings that should be constants


## Creating Custom Rules

Laravel Spotlight makes it easy to create your own custom rules. See the [Creating Rules Guide](docs/CREATING_RULES.md) for detailed instructions.

### Quick Example

Using Spotlight's clean auto-detection approach:

```php
<?php

namespace App\Spotlight\Rules\Performance; // Category auto-detected from namespace!

use Otatechie\Spotlight\Rules\AbstractRule;

class MyCustomRule extends AbstractRule
{
    // Only define what's different - everything else is auto-detected!
    protected string $severity = 'medium';
    protected string $description = 'Checks for a specific issue';
    
    // id: auto-generated as 'performance.my-custom'
    // category: auto-detected as 'performance' from namespace
    // name: auto-generated as 'My Custom' from class name

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

Register it in `config/spotlight.php`:

```php
return [
    'custom_rules' => [
        \App\Spotlight\Rules\MyCustomRule::class,
    ],
];
```

## Configuration

Publish the config file:

```bash
php artisan vendor:publish --tag="spotlight-config"
```

Key configuration options:

- `enabled_rules` - Enable/disable specific rules
- `custom_rules` - Register your own custom rules
- `severity_threshold` - Filter rules by severity level
- `debug` - Enable debug logging
- `error_handling` - Control error handling behavior

See [Examples & Configuration](docs/EXAMPLES.md) for more details.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Documentation

- [Creating Custom Rules](docs/CREATING_RULES.md) - Guide to creating your own rules
- [Examples & Configuration](docs/EXAMPLES.md) - Usage examples and configuration options
- [Laravel Best Practices](docs/LARAVEL_BEST_PRACTICES.md) - Sources and rationale behind rules

## Contributing

Contributions are welcome! Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [otatechie](https://github.com/otatechie)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Laravel Trademark Disclaimer

Laravel is a trademark of Taylor Otwell. This package is not officially associated with Laravel or Taylor Otwell. The "laravel-" prefix in the package name is used to indicate compatibility with the Laravel framework and follows community naming conventions.
