<?php

namespace Rareloop\Lumberjack\Facades;

use Blast\Facades\AbstractFacade;

class Config extends AbstractFacade
{
    protected static function accessor()
    {
        return 'config';
    }
}
