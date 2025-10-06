<?php

namespace Rareloop\Lumberjack\Test\Unit\Facades\Stubs;

use Rareloop\Lumberjack\Facades\AbstractFacade;

class FooFacade extends AbstractFacade
{
    public static function accessor(): string
    {
        return 'foo';
    }
}
