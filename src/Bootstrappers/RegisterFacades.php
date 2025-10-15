<?php

namespace Rareloop\Lumberjack\Bootstrappers;

use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\FacadeFactory;

class RegisterFacades
{
    public function bootstrap(Application $app)
    {
        FacadeFactory::setContainer($app);
    }
}
