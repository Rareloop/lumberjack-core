<?php

namespace Rareloop\Lumberjack\Bootstrappers;

use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Helpers;
use Rareloop\Lumberjack\Providers\LogServiceProvider;
use Rareloop\Lumberjack\Providers\IgnitionServiceProvider;

class RegisterProviders
{
    public function bootstrap(Application $app)
    {
        $config = $app->get('config');

        $this->registerBaseProviders($app);

        foreach ($config->get('app.providers', []) as $provider) {
            $app->register(Helpers::invoke($provider));
        }
    }

    protected function registerBaseProviders(Application $app)
    {
        $app->register(LogServiceProvider::class);
        $app->register(IgnitionServiceProvider::class);
    }
}
