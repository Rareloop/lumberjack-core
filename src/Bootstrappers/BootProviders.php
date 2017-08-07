<?php

namespace Rareloop\Lumberjack\Bootstrappers;

use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Config;

class BootProviders
{
    public function bootstrap(Application $app)
    {
        $app->boot();
    }
}
