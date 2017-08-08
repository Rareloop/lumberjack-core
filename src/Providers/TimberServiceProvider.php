<?php

namespace Rareloop\Lumberjack\Providers;

use Rareloop\Lumberjack\Application;
use Timber\Timber;

class TimberServiceProvider
{
    public function register(Application $app)
    {
        $timber = new Timber($app);

        $app->bind('timber', $timber);
        $app->bind(Timber::class, $timber);

        if ($app->has('config')) {
            $paths = $app->get('config')->get('timber.paths');

            if ($paths) {
                Timber::$dirname = $paths;
            }
        }
    }
}
