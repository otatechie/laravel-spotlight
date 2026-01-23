<?php

namespace Otatechie\Spotlight;

/**
 * Severity levels for rules using PHP 8.1+ backed enum
 */
enum SeverityLevel: string
{
    case Critical = 'critical';
    case High = 'high';
    case Medium = 'medium';
    case Low = 'low';

    /**
     * Get the numeric weight for scoring
     */
    public function weight(): int
    {
        return match ($this) {
            self::Critical => 100,
            self::High => 70,
            self::Medium => 40,
            self::Low => 10,
        };
    }

    /**
     * Get display label
     */
    public function label(): string
    {
        return match ($this) {
            self::Critical => 'Critical',
            self::High => 'High',
            self::Medium => 'Medium',
            self::Low => 'Low',
        };
    }

    /**
     * Get emoji (minimal, professional)
     */
    public function emoji(): string
    {
        return match ($this) {
            self::Critical => '❌',
            self::High => '⚠️',
            self::Medium => 'ℹ️',
            self::Low => '💡',
        };
    }

    /**
     * Get all severity levels
     *
     * @return array<SeverityLevel>
     */
    public static function all(): array
    {
        return self::cases();
    }

    /**
     * Get all severity values as strings
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_map(fn (self $case) => $case->value, self::cases());
    }

    /**
     * Check if a string is a valid severity
     */
    public static function isValid(string $severity): bool
    {
        return in_array($severity, self::values(), true);
    }

    /**
     * Create from string (with fallback)
     */
    public static function fromString(string $severity): self
    {
        return self::tryFrom($severity) ?? self::Low;
    }
}
