<?php

namespace Rareloop\Lumberjack\Facades;

use Blast\Facades\AbstractFacade;

class Config extends AbstractFacade
{
    #[\Override]
    protected static function accessor()
    {
        return 'config';
    }
}
