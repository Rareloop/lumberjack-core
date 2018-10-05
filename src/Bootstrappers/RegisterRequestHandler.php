<?php

namespace Rareloop\Lumberjack\Bootstrappers;

use Rareloop\Lumberjack\Application;

class RegisterRequestHandler
{
    public function bootstrap(Application $app)
    {
        $config = $app->get('config');

        if ($config->get('app.debug')) {
            $app->detectWhenRequestHasNotBeenHandled();
        }
    }
}
