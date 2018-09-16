<?php

namespace Rareloop\Lumberjack\Facades;

use Blast\Facades\AbstractFacade;

class Session extends AbstractFacade
{
    protected static function accessor()
    {
        return 'session';
    }
}
