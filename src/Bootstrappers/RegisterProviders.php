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

        collect($config->get('app.providers', []))
            ->map(fn($provider) => Helpers::invoke($provider))
            ->filter()
            ->each(fn($provider) => $app->register($provider));
    }

    protected function registerBaseProviders(Application $app)
    {
        $app->register(LogServiceProvider::class);
        $app->register(IgnitionServiceProvider::class);
    }
}
