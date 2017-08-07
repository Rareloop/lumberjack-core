<?php

namespace Rareloop\Lumberjack\Providers;

use Rareloop\Lumberjack\Application;
use Rareloop\Router\Router;

class RouterServiceProvider
{
    public function register(Application $app)
    {
        $router = new Router($app);

        $app->bind('router', $router);
        $app->bind(Router::class, $router);
    }
}
