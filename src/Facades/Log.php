<?php

namespace Rareloop\Lumberjack\Facades;

use Blast\Facades\AbstractFacade;

class Log extends AbstractFacade
{
    #[\Override]
    protected static function accessor()
    {
        return 'logger';
    }
}
