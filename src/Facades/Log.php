<?php

namespace Rareloop\Lumberjack\Facades;

class Log extends AbstractFacade
{
    protected static function accessor()
    {
        return 'logger';
    }
}
