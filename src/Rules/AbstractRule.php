<?php

namespace Otatechie\Spotlight\Rules;

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
     * Severity level - if not set, uses category-based defaults:
     * - security: high
     * - performance: medium
     * - architecture: low
     */
    protected ?string $severity = null;

    /**
     * Rule type - defaults to 'advisory' if not set
     * - 'objective': Firm recommendations (security, performance, misconfiguration)
     * - 'advisory': Gentle suggestions (architecture, style, structure)
     */
    protected string $type = 'advisory';

    /**
     * Rule name - auto-generated from class name if not set
     */
    protected ?string $name = null;

    /**
     * Rule description - optional, defaults to empty string
     */
    protected string $description = '';

    /**
     * Documentation URL - optional link to detailed documentation
     */
    protected ?string $documentationUrl = null;

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
        // e.g., "Otatechie\Spotlight\Rules\Performance\ConfigCacheRule" -> "performance"
        $namespace = get_class($this);
        if (preg_match('/\\\Rules\\\([^\\\\]+)\\\/', $namespace, $matches)) {
            return strtolower($matches[1]);
        }

        return 'general';
    }

    public function getSeverity(): string
    {
        if ($this->severity !== null) {
            return $this->severity;
        }

        // Category-based defaults
        return match ($this->getCategory()) {
            'security' => 'high',
            'performance' => 'medium',
            'architecture' => 'low',
            default => 'low',
        };
    }

    public function getType(): string
    {
        return $this->type;
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
     * Get the documentation URL for this rule
     */
    public function getDocumentationUrl(): ?string
    {
        return $this->documentationUrl;
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
        // Include documentation URL if available
        if ($this->getDocumentationUrl() !== null && ! isset($metadata['documentation_url'])) {
            $metadata['documentation_url'] = $this->getDocumentationUrl();
        }

        return [
            'id' => $this->getId(),
            'status' => 'suggestion',
            'message' => $message,
            'severity' => $this->getSeverity(),
            'type' => $this->getType(),
            'category' => $this->getCategory(),
            'metadata' => $metadata,
        ];
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
            'type' => $this->getType(),
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
