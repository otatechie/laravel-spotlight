<?php

use Otatechie\Spotlight\SpotlightServiceProvider;
use Illuminate\Support\Facades\Artisan;

it('registers the service provider', function () {
    $providers = app()->getLoadedProviders();

    expect($providers)->toHaveKey(SpotlightServiceProvider::class);
});

it('registers the spotlight command', function () {
    $commands = Artisan::all();

    expect($commands)->toHaveKey('spotlight:scan');
});

it('can execute the spotlight command', function () {
    Artisan::call('spotlight:scan');

    $output = Artisan::output();

    expect($output)
        ->toContain('Laravel Spotlight Scan')
        ->toContain('Summary')
        ->toContain('Health Score');
});

it('has package auto-discovery configured in composer.json', function () {
    $composer = json_decode(file_get_contents(__DIR__.'/../composer.json'), true);

    expect($composer['extra']['laravel']['providers'])
        ->toContain('Otatechie\\Spotlight\\SpotlightServiceProvider');
});
