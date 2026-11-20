<?php

namespace Rareloop\Lumberjack\Providers;

use Rareloop\Lumberjack\Config;
use Rareloop\Lumberjack\Helpers;
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
        $paths = Helpers::invoke($config->get('timber.paths'));

        if (is_array($paths)) {
            $paths = collect($paths)->map(fn($path) => Helpers::invoke($path))->all();
        }

        if ($paths) {
            Timber::$dirname = $paths;
        }
    }
}
