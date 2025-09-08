<?php

namespace Rareloop\Lumberjack\Bootstrappers;

use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\FacadeManager;

class RegisterFacades
{
    public function bootstrap(Application $app)
    {
        FacadeManager::setContainer($app);
    }
}
