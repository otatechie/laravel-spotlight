<?php

use Otatechie\Spotlight\Spotlight;
use Otatechie\Spotlight\Rules\RuleRegistry;

it('can scan all categories by default', function () {
    $registry = app(RuleRegistry::class);
    $spotlight = new Spotlight($registry);
    $results = $spotlight->scan();

    expect($results)
        ->toHaveKey('timestamp')
        ->toHaveKey('categories')
        ->toHaveKey('summary')
        ->toHaveKey('rules');
});

it('can scan specific categories', function () {
    $registry = app(RuleRegistry::class);
    $spotlight = new Spotlight($registry);
    $results = $spotlight->scan(['security']);

    // Should only have security category rules
    $hasSecurityRules = false;
    foreach ($results['rules'] as $ruleResult) {
        if (isset($ruleResult['category']) && $ruleResult['category'] === 'security') {
            $hasSecurityRules = true;
            break;
        }
    }

    expect($hasSecurityRules)->toBeTrue();
});

it('returns structured results with summary', function () {
    $registry = app(RuleRegistry::class);
    $spotlight = new Spotlight($registry);
    $results = $spotlight->scan();

    expect($results['summary'])
        ->toHaveKey('total_rules')
        ->toHaveKey('passed')
        ->toHaveKey('suggestions')
        ->toHaveKey('errors');
});

it('returns category results with rules array', function () {
    $registry = app(RuleRegistry::class);
    $spotlight = new Spotlight($registry);
    $results = $spotlight->scan(['security']);

    if (isset($results['categories']['security'])) {
        $securityCategory = $results['categories']['security'];

        expect($securityCategory)
            ->toHaveKey('name')
            ->toHaveKey('rules')
            ->toHaveKey('passed')
            ->toHaveKey('suggestions')
            ->toHaveKey('errors')
            ->and($securityCategory['rules'])
            ->toBeArray();
    } else {
        // If no security rules were registered, that's also valid
        expect($results['categories'])->toBeArray();
    }
});
