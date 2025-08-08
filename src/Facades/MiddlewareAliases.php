<?php

namespace Rareloop\Lumberjack\Facades;

use Illuminate\Support\Facades\Facade;

class MiddlewareAliases extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'middleware-alias-store';
    }
}
