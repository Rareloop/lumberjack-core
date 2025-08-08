<?php

namespace Rareloop\Lumberjack\Bootstrappers;

use Illuminate\Support\Facades\Facade;
use Rareloop\Lumberjack\Application;

class RegisterFacades
{
    public function bootstrap(Application $app)
    {
        Facade::setFacadeApplication($app);
    }
}
