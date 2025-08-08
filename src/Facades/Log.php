<?php

namespace Rareloop\Lumberjack\Facades;

use Illuminate\Support\Facades\Facade;

class Log extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'logger';
    }
}
