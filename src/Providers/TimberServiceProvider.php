<?php

namespace Rareloop\Lumberjack\Providers;

use Rareloop\Lumberjack\Application;
use Timber\Timber;

class TimberServiceProvider extends ServiceProvider
{
    public function register()
    {
        $timber = new Timber($this->app);

        $this->app->bind('timber', $timber);
        $this->app->bind(Timber::class, $timber);

        if ($this->app->has('config')) {
            $paths = $this->app->get('config')->get('timber.paths');

            if ($paths) {
                Timber::$dirname = $paths;
            }
        }
    }
}
