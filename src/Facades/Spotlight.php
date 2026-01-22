<?php

namespace Otatechie\Spotlight\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Otatechie\Spotlight\Spotlight
 */
class Spotlight extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Otatechie\Spotlight\Spotlight::class;
    }
}
