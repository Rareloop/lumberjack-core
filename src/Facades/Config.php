<?php

namespace Rareloop\Lumberjack\Facades;

use Illuminate\Support\Facades\Facade;

class Config extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'config';
    }
}
