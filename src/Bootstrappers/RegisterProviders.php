<?php

namespace Rareloop\Lumberjack\Bootstrappers;

use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Providers\LogServiceProvider;

class RegisterProviders
{
    public function bootstrap(Application $app)
    {
        $config = $app->get('config');

        $this->registerBaseProviders($app);

        $providers = $config->get('app.providers', []);

        foreach ($providers as $provider) {
            $app->register($provider);
        }
    }

    protected function registerBaseProviders(Application $app)
    {
        $app->register(LogServiceProvider::class);
    }
}
