<?php

namespace Otatechie\Spotlight\Commands;

use Illuminate\Console\Command;
use Otatechie\Spotlight\Rules\RuleRegistry;

class ListRulesCommand extends Command
{
    public $signature = 'spotlight:rules
                        {--rule= : Show details for a specific rule}';

    public $description = 'List all available Spotlight rules';

    public function handle(RuleRegistry $registry): int
    {
        $specificRule = $this->option('rule');

        if ($specificRule) {
            return $this->showRuleDetails($registry, $specificRule);
        }

        return $this->listAllRules($registry);
    }

    protected function listAllRules(RuleRegistry $registry): int
    {
        $this->info('📋 Available Spotlight Rules');
        $this->newLine();

        $rules = $registry->all();
        $categories = $registry->getCategories();

        foreach ($categories as $category) {
            $categoryRules = $registry->byCategory($category);

            $this->info('📁 '.ucfirst($category).' ('.count($categoryRules).' rules)');

            foreach ($categoryRules as $rule) {
                $type = $rule->getType() === 'objective' ? '🔒' : '💡';
                $severity = match ($rule->getSeverity()) {
                    'critical' => '🔴',
                    'warning' => '⚠️',
                    default => 'ℹ️',
                };

                $this->line("  {$type} {$severity} {$rule->getId()}");
                $this->comment("     {$rule->getName()}");
            }

            $this->newLine();
        }

        $this->comment("Use 'php artisan spotlight:rules --rule=<rule-id>' for detailed information");

        return self::SUCCESS;
    }

    protected function showRuleDetails(RuleRegistry $registry, string $ruleId): int
    {
        $rule = $registry->get($ruleId);

        if (! $rule) {
            $this->error("Rule '{$ruleId}' not found.");
            $this->comment("Use 'php artisan spotlight:rules' to see all available rules.");

            return self::FAILURE;
        }

        $this->info("📖 Rule Details: {$rule->getId()}");
        $this->newLine();

        $this->table(
            ['Property', 'Value'],
            [
                ['Name', $rule->getName()],
                ['Description', $rule->getDescription()],
                ['Category', ucfirst($rule->getCategory())],
                ['Type', $rule->getType() === 'objective' ? 'Objective (Firm recommendation)' : 'Advisory (Gentle suggestion)'],
                ['Severity', ucfirst($rule->getSeverity())],
                ['Class', get_class($rule)],
            ]
        );

        $this->newLine();
        $this->comment('To disable this rule, add to config/spotlight.php:');
        $this->line("'enabled_rules' => [");
        $this->line("    '{$ruleId}' => false,");
        $this->line('],');

        return self::SUCCESS;
    }
}
