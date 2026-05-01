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

            $ignition = Ignition::make()
                ->shouldDisplayException($config->get('app.debug', false) === true)
                ->runningInProductionEnvironment($config->get('app.env') === 'production');

            $ignition->setConfig(new IgnitionConfig($ignitionConfigArray));
            $ignition->register();

            return $ignition;
        });
    }
}
