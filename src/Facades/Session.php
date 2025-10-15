<?php

namespace Rareloop\Lumberjack\Facades;

class Session extends AbstractFacade
{
    protected static function accessor()
    {
        return 'session';
    }
}
