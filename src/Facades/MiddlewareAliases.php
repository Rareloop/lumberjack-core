<?php

namespace Rareloop\Lumberjack\Facades;

use Blast\Facades\AbstractFacade;

class MiddlewareAliases extends AbstractFacade
{
    protected static function accessor()
    {
        return 'middleware-alias-store';
    }
}
