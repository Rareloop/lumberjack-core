<?php

namespace Rareloop\Lumberjack\Providers;

use Spatie\Ignition\Ignition;
use Spatie\Ignition\Config\IgnitionConfig;

class IgnitionServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(Ignition::class, function () {
            $config = $this->app->get('config');
            $ignitionConfigArray = $config->get('ignition') ?: [];

            $debug = $config->get('app.debug', false);

            $ignition = Ignition::make()
                ->shouldDisplayException($debug === true)
                ->runningInProductionEnvironment($debug !== true);

            $ignition->setConfig(new IgnitionConfig($ignitionConfigArray));
            $ignition->register();

            return $ignition;
        });
    }
}
