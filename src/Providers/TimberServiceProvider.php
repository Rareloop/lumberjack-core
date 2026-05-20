<?php

namespace Rareloop\Lumberjack\Providers;

use Rareloop\Lumberjack\Config;
use Rareloop\Lumberjack\Http\TimberContext;
use Timber\Timber;

class TimberServiceProvider extends ServiceProvider
{
    public function register()
    {
        Timber::init();

        $this->app->singleton(TimberContext::class, fn() => new TimberContext(Timber::context()));
    }

    public function boot(Config $config)
    {
        $paths = $config->get('timber.paths');

        if ($paths) {
            Timber::$dirname = $paths;
        }
    }
}
