# Why Laravel Spotlight?

This document explains what makes Laravel Spotlight unique and why we built it this way.

## Our Philosophy

**Spotlight provides guidance, not enforcement.**

We believe diagnostic tools should be:

- ✅ **Supportive** - Help developers improve without judgment
- ✅ **Flexible** - Adapt to your project's unique needs
- ✅ **Fast** - Quick enough to use regularly
- ✅ **Simple** - Easy to understand and extend
- ✅ **Respectful** - You know your codebase best

## What Makes Spotlight Different

### 1. 🎯 **Friendly Mentor Approach**

Spotlight uses encouraging, non-judgmental language:

```
💡 [MEDIUM] Config cache could improve performance in production
→ Run `php artisan config:cache` to cache your configuration
```

**Our approach:** Spotlight informs and suggests. We explain *why* something matters, not just that it's "wrong."

### 2. 🎛️ **Full User Control**

Every rule can be enabled or disabled in your config:

```php
// config/spotlight.php
'enabled_rules' => [
    'architecture.large-controller' => false, // Disable if not applicable
],
```

**Our approach:** You know your project best. Spotlight gives you control.

### 3. 📊 **Distinguishes Severity & Type**

Not all issues are equal. Spotlight separates:

- **Objective Rules** - Firm recommendations (security, performance)
  - These are generally agreed-upon best practices
- **Advisory Rules** - Gentle suggestions (architecture, style)
  - These are helpful tips that may not apply to every project

Plus, severity levels help you prioritize:
- `critical` → Production-breaking / security risk
- `high` → Serious performance or stability issue
- `medium` → Worth addressing when possible
- `low` → Minor improvement / suggestion

### 4. ⚡ **Fast Execution**

Spotlight is designed for speed:

- Lightweight checks (config, file existence, patterns)
- No database queries during scans
- No external API calls
- **Target: < 1 second total execution**

**Our approach:** Fast tools get used regularly. We want Spotlight in your daily workflow.

### 5. 🧩 **Easy to Extend**

Creating custom rules is simple:

```php
class MyRule extends AbstractRule
{
    protected string $description = 'Checks something';
    
    public function scan(): array
    {
        return $this->suggest('Issue found', [
            'recommendation' => 'How to fix'
        ]);
    }
}
```

**Our approach:** Convention over configuration. The ID, category, name, and severity are auto-detected from class name and namespace.

### 6. 🛡️ **Graceful Error Handling**

If one rule throws an exception, the scan continues:

- Single rule failures don't crash the entire scan
- Errors are logged and reported
- Configurable: `continue` (default) or `stop` on error

### 7. 📖 **Educational Focus**

Rules can include documentation URLs to help developers learn:

```php
protected ?string $documentationUrl = 'https://laravel.com/docs/cache';
```

We want to help developers understand *why*, not just *what*.

## Comparison with Other Approaches

| Aspect | Traditional Approach | Spotlight's Approach |
|--------|---------------------|---------------------|
| **Tone** | "Fix this now" | "Consider this improvement" |
| **Control** | Take it or leave it | Full enable/disable per rule |
| **Speed** | Thorough but slow | Fast enough for every commit |
| **Types** | All issues equal | Objective vs Advisory distinction |
| **Extension** | Complex APIs | Simple convention-based rules |
| **Errors** | May crash on issues | Graceful continuation |

## When to Use Spotlight

Spotlight is great for:

✅ **Quick health checks** - Run before deployments  
✅ **CI/CD pipelines** - Fast enough for every commit  
✅ **Learning** - Educational messages help developers grow  
✅ **Custom architectures** - Disable rules that don't fit  
✅ **Production preparation** - Catch security and performance issues  

## Complementary Tools

Spotlight is **not** a replacement for these wonderful tools:

| Tool | Purpose | How Spotlight Differs |
|------|---------|----------------------|
| **Laravel Debugbar** | Runtime debugging | Spotlight is static analysis |
| **Laravel Telescope** | Application monitoring | Spotlight is one-time scans |
| **PHPStan/Larastan** | Type checking | Spotlight is pattern-based |
| **Laravel Pint** | Code formatting | Spotlight is diagnostics |

These tools work great alongside Spotlight!

## Summary

Laravel Spotlight is built on these principles:

1. **Respect** - Developers know their projects
2. **Flexibility** - Adapts to different needs
3. **Speed** - Fast enough for daily use
4. **Simplicity** - Easy to understand and extend
5. **Kindness** - Helpful guidance, never judgment

---

*"We're here to help you build better Laravel applications, one friendly suggestion at a time."*
