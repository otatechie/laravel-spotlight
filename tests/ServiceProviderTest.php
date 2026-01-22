<?php

use AtoAugustine\Beacon\BeaconServiceProvider;
use Illuminate\Support\Facades\Artisan;

it('registers the service provider', function () {
    $providers = app()->getLoadedProviders();

    expect($providers)->toHaveKey(BeaconServiceProvider::class);
});

it('registers the beacon command', function () {
    $commands = Artisan::all();

    expect($commands)->toHaveKey('beacon:scan');
});

it('can execute the beacon command', function () {
    Artisan::call('beacon:scan');

    $output = Artisan::output();

    expect($output)
        ->toContain('Beacon')
        ->toContain('Analyzing your application')
        ->toContain('Scan Results');
});

it('has package auto-discovery configured in composer.json', function () {
    $composer = json_decode(file_get_contents(__DIR__.'/../composer.json'), true);

    expect($composer['extra']['laravel']['providers'])
        ->toContain('AtoAugustine\\Beacon\\BeaconServiceProvider');
});
