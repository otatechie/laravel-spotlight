# Laravel Beacon

A lighthouse-style diagnostics tool that scans Laravel applications for performance, security, and architecture issues. Built with a modular rule system that makes it easy to extend and customize.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/otatechie/laravel-beacon.svg?style=flat-square)](https://packagist.org/packages/otatechie/laravel-beacon)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/otatechie/laravel-beacon/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/otatechie/laravel-beacon/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/otatechie/laravel-beacon/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/otatechie/laravel-beacon/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/otatechie/laravel-beacon.svg?style=flat-square)](https://packagist.org/packages/otatechie/laravel-beacon)

Laravel Beacon helps you identify and fix issues in your Laravel application before they become problems. It scans your application for:

- **Performance Issues**: Missing caches, inefficient queue drivers, N+1 queries
- **Security Vulnerabilities**: Debug mode in production, insecure configurations
- **Architecture Problems**: Fat controllers, route closures, code organization

## Features

- 🔍 **Comprehensive Scanning**: Automatically scans your Laravel application for common issues
- 🧩 **Modular Rule System**: Easy to extend with custom rules
- ⚙️ **Configurable**: Enable/disable rules, set severity levels, add custom rules
- 🛡️ **Error Handling**: Robust error handling prevents single rule failures from crashing scans
- 📊 **Multiple Output Formats**: Table and JSON output formats
- 🎯 **Category Filtering**: Scan specific categories or all at once


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

### Programmatic Usage

```php
use AtoAugustine\Beacon\Beacon;

$beacon = app(Beacon::class);
$results = $beacon->scan(['performance', 'security']);

// Access results
$summary = $results['summary'];
$categories = $results['categories'];
```

## Built-in Rules

### Performance Rules

- **Config Cache**: Checks if config cache is enabled in production
- **Route Cache**: Checks if route cache is enabled in production
- **Queue Sync Driver**: Warns about sync queue driver in production

### Security Rules

- **App Debug Enabled**: Critical check for debug mode in production
- **Insecure Session Driver**: Warns about insecure session drivers

### Architecture Rules

- **Route Closure Usage**: Detects route closures preventing caching
- **Fat Controller Detection**: Identifies controllers exceeding 300 lines

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
        return 'warning';
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
            return $this->fail(
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
