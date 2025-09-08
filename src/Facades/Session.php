<?php

namespace Rareloop\Lumberjack\Facades;

use Blast\Facades\AbstractFacade;

class Session extends Facade
{
    protected static function accessor()
    {
        return 'session';
    }
}
