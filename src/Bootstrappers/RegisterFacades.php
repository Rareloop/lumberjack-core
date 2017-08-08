<?php

namespace Rareloop\Lumberjack\Bootstrappers;

use Blast\Facades\FacadeFactory;
use Rareloop\Lumberjack\Application;

class RegisterFacades
{
    public function bootstrap(Application $app)
    {
        FacadeFactory::setContainer($app);
    }
}
