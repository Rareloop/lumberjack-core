<?php

namespace Rareloop\Lumberjack\Facades;

class Log extends Facade
{
    protected static function accessor()
    {
        return 'logger';
    }
}
