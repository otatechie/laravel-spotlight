<?php

namespace Otatechie\Spotlight\Rules;

class RuleRegistry
{
    /**
     * @var array<string, RuleInterface>
     */
    protected array $rules = [];

    /**
     * Register a rule
     *
     * @param  RuleInterface|string  $rule  Rule instance or class name
     * @return $this
     */
    public function register(RuleInterface|string $rule): self
    {
        if (is_string($rule)) {
            $rule = app($rule);
        }

        if (! $rule instanceof RuleInterface) {
            throw new \InvalidArgumentException('Rule must implement RuleInterface');
        }

        $this->rules[$rule->getId()] = $rule;

        return $this;
    }

    /**
     * Register multiple rules
     *
     * @param  array<RuleInterface|string>  $rules
     * @return $this
     */
    public function registerMany(array $rules): self
    {
        foreach ($rules as $rule) {
            $this->register($rule);
        }

        return $this;
    }

    /**
     * Get all registered rules
     *
     * @return array<string, RuleInterface>
     */
    public function all(): array
    {
        return $this->rules;
    }

    /**
     * Get rules by category
     *
     * @return array<string, RuleInterface>
     */
    public function byCategory(string $category): array
    {
        return array_filter(
            $this->rules,
            fn (RuleInterface $rule) => $rule->getCategory() === $category
        );
    }

    /**
     * Get rules by severity
     *
     * @return array<string, RuleInterface>
     */
    public function bySeverity(string $severity): array
    {
        return array_filter(
            $this->rules,
            fn (RuleInterface $rule) => $rule->getSeverity() === $severity
        );
    }

    /**
     * Get a specific rule by ID
     */
    public function get(string $id): ?RuleInterface
    {
        return $this->rules[$id] ?? null;
    }

    /**
     * Check if a rule is registered
     */
    public function has(string $id): bool
    {
        return isset($this->rules[$id]);
    }

    /**
     * Get all unique categories
     *
     * @return array<string>
     */
    public function getCategories(): array
    {
        $categories = array_map(
            fn (RuleInterface $rule) => $rule->getCategory(),
            $this->rules
        );

        return array_unique($categories);
    }

    /**
     * Clear all registered rules
     *
     * @return $this
     */
    public function clear(): self
    {
        $this->rules = [];

        return $this;
    }
}
