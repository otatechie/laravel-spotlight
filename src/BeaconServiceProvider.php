<?php

namespace AtoAugustine\Beacon;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use AtoAugustine\Beacon\Commands\BeaconCommand;

class BeaconServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-beacon')
            ->hasConfigFile()
            ->hasViews()
            ->hasCommand(BeaconCommand::class);
    }
}
