<?php

namespace Rareloop\Lumberjack\Facades;

use Blast\Facades\AbstractFacade;

class Router extends AbstractFacade
{
    protected static function accessor()
    {
        return 'router';
    }
}
