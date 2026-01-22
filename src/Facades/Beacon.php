<?php

namespace AtoAugustine\Beacon\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \AtoAugustine\Beacon\Beacon
 */
class Beacon extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \AtoAugustine\Beacon\Beacon::class;
    }
}
