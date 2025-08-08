<?php

namespace Rareloop\Lumberjack\Facades;

use Illuminate\Support\Facades\Facade;

class Session extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'session';
    }
}
