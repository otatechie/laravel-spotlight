<?php

namespace Otatechie\Spotlight\Rules;

interface RuleInterface
{
    /**
     * Get the unique identifier for this rule
     */
    public function getId(): string;

    /**
     * Get the category this rule belongs to
     */
    public function getCategory(): string;

    /**
     * Get the severity level (critical, warning, info)
     */
    public function getSeverity(): string;

    /**
     * Get the rule type (objective or advisory)
     * - objective: Firm recommendations for security/performance issues
     * - advisory: Gentle suggestions for architecture/style improvements
     */
    public function getType(): string;

    /**
     * Get the rule name/title
     */
    public function getName(): string;

    /**
     * Get the rule description
     */
    public function getDescription(): string;

    /**
     * Get the documentation URL for this rule (optional)
     */
    public function getDocumentationUrl(): ?string;

    /**
     * Execute the rule and return result
     *
     * @return array<string, mixed>
     */
    public function scan(): array;
}
