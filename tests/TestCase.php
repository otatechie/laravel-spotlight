<?php

namespace Otatechie\Spotlight\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Otatechie\Spotlight\SpotlightServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [
            SpotlightServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');
    }
}
