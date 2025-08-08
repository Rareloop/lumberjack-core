<?php

namespace Rareloop\Lumberjack\Facades;

use Illuminate\Support\Facades\Facade;

class Router extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'router';
    }
}
