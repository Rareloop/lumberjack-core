<?php

namespace Rareloop\Lumberjack\Facades;

use Blast\Facades\AbstractFacade;

class Session extends AbstractFacade
{
    #[\Override]
    protected static function accessor()
    {
        return 'session';
    }
}
