# Creating Custom Rules for Laravel Spotlight

This guide will help you create custom rules for Laravel Spotlight to scan your application for specific issues.

## Rule Structure

All rules must implement the `Otatechie\Spotlight\Rules\RuleInterface` interface. The easiest way to create a rule is to extend the `AbstractRule` base class.

## Rule Types

Spotlight supports two rule types:

- **Objective** (`type: 'objective'`) - Firm recommendations for security, performance, misconfigurations
- **Advisory** (`type: 'advisory'`) - Gentle suggestions for architecture, style, structure

See the "Rule Design Guidelines" section below for detailed guidance on when to use each type.

## Basic Rule Example (Clean & DRY)

Laravel Spotlight uses convention-based auto-detection! You only need to define what's different:

```php
<?php

namespace App\Spotlight\Rules\Performance; // Category auto-detected from namespace!

use Otatechie\Spotlight\Rules\AbstractRule;

class MyCustomRule extends AbstractRule
{
    // Only override what's different - everything else is auto-detected!
    protected ?string $name = 'My Custom Rule'; // Optional: auto-generated from class name
    protected string $description = 'Checks for a specific issue in my application';
    protected string $severity = 'warning'; // Optional: defaults to 'info'
    
    // id: auto-generated as 'performance.my-custom' (from namespace + class name)
    // category: auto-detected as 'performance' (from namespace)

    public function scan(): array
    {
        // Your scanning logic here
        
        if ($issueFound) {
            return $this->suggest(
                'Issue description here',
                [
                    'recommendation' => 'How to fix the issue',
                    'additional_data' => 'Any extra metadata',
                ]
            );
        }

        return $this->pass('Everything looks good!');
    }
}
```

**That's it!** No need to implement `getId()`, `getCategory()`, etc. - they're auto-detected!

See the "Auto-Detection" section below for detailed auto-detection rules.

## Helper Methods

The `AbstractRule` class provides two helper methods:

### `fail(string $message, array $metadata = [])`

Returns a failure result. Use this when your rule detects an issue.

```php
return $this->fail(
    'Config file is missing',
    [
        'recommendation' => 'Create the config file at config/myapp.php',
        'file' => 'config/myapp.php',
    ]
);
```

### `pass(string $message = '', array $metadata = [])`

Returns a pass result. Use this when your rule finds no issues.

```php
return $this->pass('All checks passed');
```

## Result Structure

Both `fail()` and `pass()` return an array with the following structure:

```php
[
    'id' => 'rule-id',
    'status' => 'passed' | 'failed',
    'message' => 'Human-readable message',
    'severity' => 'info' | 'warning' | 'critical',
    'category' => 'category-name',
    'metadata' => [
        // Your custom metadata
    ],
]
```

## Registering Custom Rules

### Method 1: Via Config File

Add your rule class to the `custom_rules` array in `config/spotlight.php`:

```php
return [
    'custom_rules' => [
        \App\Spotlight\Rules\MyCustomRule::class,
    ],
];
```

### Method 2: Via Service Provider

Register your rule in a service provider:

```php
use Otatechie\Spotlight\Rules\RuleRegistry;

public function boot()
{
    $registry = app(RuleRegistry::class);
    $registry->register(\App\Spotlight\Rules\MyCustomRule::class);
}
```

## Rule Categories

You can use existing categories or create your own:

- `performance` - Performance-related issues
- `security` - Security vulnerabilities
- `architecture` - Code architecture and organization
- `custom` - Your own custom category

## Severity Levels

Choose the appropriate severity level:

- `info` - Informational, not critical
- `warning` - Should be addressed but not urgent
- `critical` - Must be fixed immediately

## Advanced Example

Here's a more complex example that checks multiple conditions:

```php
<?php

namespace App\Spotlight\Rules;

use Otatechie\Spotlight\Rules\AbstractRule;
use Illuminate\Support\Facades\File;

class ComplexRule extends AbstractRule
{
    public function getId(): string
    {
        return 'custom.complex-check';
    }

    public function getCategory(): string
    {
        return 'performance';
    }

    public function getSeverity(): string
    {
        return 'warning';
    }

    public function getName(): string
    {
        return 'Complex Performance Check';
    }

    public function getDescription(): string
    {
        return 'Checks multiple performance-related conditions';
    }

    public function scan(): array
    {
        $issues = [];
        
        // Check 1
        if (! File::exists(storage_path('cache'))) {
            $issues[] = 'Cache directory missing';
        }
        
        // Check 2
        if (config('app.debug')) {
            $issues[] = 'Debug mode enabled';
        }
        
        if (! empty($issues)) {
            return $this->fail(
                'Found ' . count($issues) . ' issue(s): ' . implode(', ', $issues),
                [
                    'issues' => $issues,
                    'recommendation' => 'Fix the issues listed above',
                ]
            );
        }

        return $this->pass('All performance checks passed');
    }
}
```

## Testing Your Rules

You can test your rules by running:

```bash
php artisan spotlight:scan --category=custom
```

Or test all rules:

```bash
php artisan spotlight:scan
```

## Best Practices

1. **Use descriptive IDs**: Make rule IDs unique and descriptive (e.g., `custom.my-feature-check`)
2. **Provide recommendations**: Always include a `recommendation` in metadata when a rule fails
3. **Handle errors gracefully**: Your rule should not throw exceptions - let Spotlight handle that
4. **Keep rules focused**: Each rule should check one specific thing
5. **Use appropriate severity**: Don't mark everything as critical
6. **Add metadata**: Include useful information in the metadata array

## Disabling Rules

You can disable rules in `config/spotlight.php`:

```php
return [
    'enabled_rules' => [
        'custom.my-rule' => false, // Disable this rule
    ],
];
```

## Rule Design Guidelines

### Philosophy

**Spotlight provides guidance, not enforcement.**

Rules should inform and suggest, not judge or shame. We aim to be a friendly mentor, not an angry linter.

### Performance Guidelines

**Rules MUST be fast.** Spotlight should feel instant, not slow.

**✅ DO:**
- Check config values: `config('app.debug')`
- Check file existence: `file_exists(base_path('...'))`
- Use Laravel APIs: `config()`, `env()`, `app()`
- Lightweight file checks: Count lines, check for patterns

**❌ DON'T:**
- Scan entire `vendor/` directory
- Parse thousands of files
- Make HTTP/API calls
- Hit the database unnecessarily

**Performance Targets:**
- Single rule: < 50ms
- Full scan: < 500ms
- Total execution: < 1 second

### Tone & Language

**✅ Good Examples:**
- "Config cache could improve performance in production"
- "Consider using Form Request classes for validation"
- "Debug mode is enabled - this exposes sensitive information"

**❌ Avoid:**
- "You MUST fix this"
- "This is WRONG"
- "Bad practice detected"

### Language Guidelines

1. **Use "could" and "consider"** - Not "must" or "should"
2. **Explain the why** - Help users understand the benefit
3. **Provide actionable recommendations** - Tell them how to fix it
4. **Be specific** - Include file names, line counts, etc. when helpful

### Severity Levels

- **`critical`** - Security vulnerabilities that must be addressed
- **`high`** - Serious performance or stability issues
- **`medium`** - Degraded behavior / suboptimal config
- **`low`** - Minor improvement / suggestion

### Checklist

Before submitting a rule, ensure:

- [ ] Rule type is set correctly (`objective` or `advisory`)
- [ ] Severity is appropriate (`critical`, `high`, `medium`, or `low`)
- [ ] Rule executes quickly (< 50ms)
- [ ] Language is friendly and non-judgmental
- [ ] Recommendation is actionable
- [ ] Rule can be disabled via config
- [ ] Error handling is in place

## Need Help?

- Check existing rules in `src/Rules/` for examples
- Review the `AbstractRule` class for available methods
- See [MINIMAL_RULE_EXAMPLE.php](MINIMAL_RULE_EXAMPLE.php) for the simplest possible rule
- See [EXAMPLE_CUSTOM_RULE.php](EXAMPLE_CUSTOM_RULE.php) for a more detailed example
