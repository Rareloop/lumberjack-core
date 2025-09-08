<?php

namespace Rareloop\Lumberjack\Test\Unit\Facades\Stubs;

use Rareloop\Lumberjack\Facades\Facade;

class FooFacade extends Facade
{
    public static function accessor(): string
    {
        return 'foo';
    }
}
