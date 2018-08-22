<?php

namespace Rareloop\Lumberjack\Bootstrappers;

use Rareloop\Lumberjack\Application;

class RegisterAliases
{
    public function bootstrap(Application $app)
    {
        $config = $app->get('config');

        foreach ($config->get('app.aliases', []) as $alias => $realClassname) {
            class_alias($realClassname, $alias);
        }
    }
}
