<?php

namespace Rareloop\Lumberjack\Bootstrappers;

use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Autodiscovery\AutodiscoveredPackages;
use Rareloop\Lumberjack\Providers\LogServiceProvider;
use Rareloop\Lumberjack\Providers\IgnitionServiceProvider;
use Illuminate\Support\Collection;

class RegisterProviders
{
    public function bootstrap(Application $app)
    {
        $config = $app->get('config');

        $this->registerBaseProviders($app);

        $manifest = $app->get(AutodiscoveredPackages::class);

        $providers = Collection::make($manifest->providers())
            ->concat($config->get('app.providers', []))
            ->mapWithKeys(function ($provider) {
                return [
                    (is_string($provider) ? $provider : $provider::class) => $provider,
                ];
            });

        foreach ($providers as $provider) {
            $app->register($provider);
        }
    }

    protected function registerBaseProviders(Application $app)
    {
        $app->register(LogServiceProvider::class);
        $app->register(IgnitionServiceProvider::class);
    }
}
