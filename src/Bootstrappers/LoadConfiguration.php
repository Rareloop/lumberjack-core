<?php

namespace Rareloop\Lumberjack\Bootstrappers;

use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Config;

class LoadConfiguration
{
    public function bootstrap(Application $app)
    {
        $config = new Config($app->configPath());

        $app->bind('config', $config);
        $app->bind(Config::class, $config);
    }
}
