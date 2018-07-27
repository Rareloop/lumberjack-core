<?php

namespace Rareloop\Lumberjack\Providers;

use Rareloop\Lumberjack\Config;
use Timber\Timber;

class TimberServiceProvider extends ServiceProvider
{
    public function register()
    {
        $timber = new Timber();

        $this->app->bind('timber', $timber);
        $this->app->bind(Timber::class, $timber);
    }

    public function boot(Config $config)
    {
        $paths = $config->get('timber.paths');

        if ($paths) {
            Timber::$dirname = $paths;
        }
    }
}
