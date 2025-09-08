<?php

namespace Rareloop\Lumberjack\Facades;

class Session extends Facade
{
    protected static function accessor()
    {
        return 'session';
    }
}
