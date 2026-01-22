<?php

namespace AtoAugustine\Beacon;

class Severity
{
    /**
     * Severity levels with their numeric weights
     */
    public const CRITICAL = 'critical';

    public const HIGH = 'high';

    public const MEDIUM = 'medium';

    public const LOW = 'low';

    /**
     * Severity weights for scoring
     */
    public const WEIGHTS = [
        self::CRITICAL => 100,
        self::HIGH => 70,
        self::MEDIUM => 40,
        self::LOW => 10,
    ];

    /**
     * Get all valid severity levels
     *
     * @return array<string>
     */
    public static function all(): array
    {
        return [
            self::CRITICAL,
            self::HIGH,
            self::MEDIUM,
            self::LOW,
        ];
    }

    /**
     * Get weight for a severity level
     */
    public static function weight(string $severity): int
    {
        return self::WEIGHTS[$severity] ?? 0;
    }

    /**
     * Check if severity is valid
     */
    public static function isValid(string $severity): bool
    {
        return in_array($severity, self::all(), true);
    }

    /**
     * Get display label for severity
     */
    public static function label(string $severity): string
    {
        return match ($severity) {
            self::CRITICAL => 'Critical',
            self::HIGH => 'High',
            self::MEDIUM => 'Medium',
            self::LOW => 'Low',
            default => ucfirst($severity),
        };
    }

    /**
     * Get emoji for severity (minimal, professional)
     */
    public static function emoji(string $severity): string
    {
        return match ($severity) {
            self::CRITICAL => '❌',
            self::HIGH => '⚠️',
            self::MEDIUM => 'ℹ️',
            self::LOW => '💡',
            default => '📝',
        };
    }
}
