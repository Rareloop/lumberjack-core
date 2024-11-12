<?php

namespace Rareloop\Lumberjack\Providers;

use Rareloop\Lumberjack\Config;
use Timber\Timber;

class TimberServiceProvider extends ServiceProvider
{
    public function register()
    {
        Timber::init();
    }

    public function boot(Config $config)
    {
        $paths = $config->get('timber.paths');

        if ($paths) {
            Timber::$dirname = $paths;
        }
    }
}
