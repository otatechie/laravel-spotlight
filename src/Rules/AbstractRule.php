<?php

namespace AtoAugustine\Beacon\Rules;

abstract class AbstractRule implements RuleInterface
{
    /**
     * Rule ID - auto-generated from class name if not set
     * Format: category.rule-name (e.g., 'performance.config-cache')
     */
    protected ?string $id = null;

    /**
     * Category - auto-detected from namespace if not set
     * (Performance, Security, Architecture)
     */
    protected ?string $category = null;

    /**
     * Severity level - defaults to 'info' if not set
     */
    protected string $severity = 'info';

    /**
     * Rule name - auto-generated from class name if not set
     */
    protected ?string $name = null;

    /**
     * Rule description - optional, defaults to empty string
     */
    protected string $description = '';

    public function getId(): string
    {
        if ($this->id !== null) {
            return $this->id;
        }

        // Auto-generate from class name
        // e.g., "ConfigCacheRule" -> "performance.config-cache"
        $className = class_basename(static::class);
        $category = $this->getCategory();
        $ruleName = $this->kebabCase($this->removeSuffix($className, 'Rule'));

        return "{$category}.{$ruleName}";
    }

    public function getCategory(): string
    {
        if ($this->category !== null) {
            return $this->category;
        }

        // Auto-detect from namespace
        // e.g., "AtoAugustine\Beacon\Rules\Performance\ConfigCacheRule" -> "performance"
        $namespace = get_class($this);
        if (preg_match('/\\\Rules\\\([^\\\\]+)\\\/', $namespace, $matches)) {
            return strtolower($matches[1]);
        }

        return 'general';
    }

    public function getSeverity(): string
    {
        return $this->severity;
    }

    public function getName(): string
    {
        if ($this->name !== null) {
            return $this->name;
        }

        // Auto-generate from class name
        // e.g., "ConfigCacheRule" -> "Config Cache"
        $className = class_basename(static::class);
        $name = $this->removeSuffix($className, 'Rule');

        return $this->titleCase($name);
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Convert string to kebab-case
     */
    protected function kebabCase(string $string): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $string));
    }

    /**
     * Convert string to Title Case
     */
    protected function titleCase(string $string): string
    {
        return preg_replace('/(?<!^)([A-Z])/', ' $1', $string);
    }

    /**
     * Remove suffix from string if present
     */
    protected function removeSuffix(string $string, string $suffix): string
    {
        if (str_ends_with($string, $suffix)) {
            return substr($string, 0, -strlen($suffix));
        }

        return $string;
    }

    /**
     * Helper method to create a suggestion/observation result
     * Use this when something could be improved (non-judgmental)
     *
     * @param  string  $message  The observation message
     * @param  array<string, mixed>  $metadata  Additional metadata
     * @return array<string, mixed>
     */
    protected function suggest(string $message, array $metadata = []): array
    {
        return [
            'id' => $this->getId(),
            'status' => 'suggestion',
            'message' => $message,
            'severity' => $this->getSeverity(),
            'category' => $this->getCategory(),
            'metadata' => $metadata,
        ];
    }

    /**
     * Alias for suggest() - for backwards compatibility
     *
     * @deprecated Use suggest() instead for neutral messaging
     */
    protected function fail(string $message, array $metadata = []): array
    {
        return $this->suggest($message, $metadata);
    }

    /**
     * Helper method to create a pass result
     *
     * @param  string  $message  Optional success message
     * @param  array<string, mixed>  $metadata  Additional metadata
     * @return array<string, mixed>
     */
    protected function pass(string $message = '', array $metadata = []): array
    {
        return [
            'id' => $this->getId(),
            'status' => 'passed',
            'message' => $message ?: "{$this->getName()} check passed",
            'severity' => $this->getSeverity(),
            'category' => $this->getCategory(),
            'metadata' => $metadata,
        ];
    }

    /**
     * Execute the rule and return result
     * This method should be implemented by concrete rule classes
     *
     * @return array<string, mixed>
     */
    abstract public function scan(): array;
}
