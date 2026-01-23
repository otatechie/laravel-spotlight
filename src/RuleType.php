<?php

namespace Otatechie\Spotlight;

/**
 * Rule types using PHP 8.1+ backed enum
 *
 * - Objective: Firm recommendations for security/performance issues
 * - Advisory: Gentle suggestions for architecture/style improvements
 */
enum RuleType: string
{
    case Objective = 'objective';
    case Advisory = 'advisory';

    /**
     * Get display label
     */
    public function label(): string
    {
        return match ($this) {
            self::Objective => 'Objective',
            self::Advisory => 'Advisory',
        };
    }

    /**
     * Get description
     */
    public function description(): string
    {
        return match ($this) {
            self::Objective => 'Firm recommendations for security and performance issues',
            self::Advisory => 'Gentle suggestions for architecture and style improvements',
        };
    }

    /**
     * Check if this type represents a firm recommendation
     */
    public function isFirm(): bool
    {
        return $this === self::Objective;
    }

    /**
     * Get all rule types
     *
     * @return array<RuleType>
     */
    public static function all(): array
    {
        return self::cases();
    }

    /**
     * Get all type values as strings
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_map(fn (self $case) => $case->value, self::cases());
    }

    /**
     * Create from string (with fallback)
     */
    public static function fromString(string $type): self
    {
        return self::tryFrom($type) ?? self::Advisory;
    }
}
