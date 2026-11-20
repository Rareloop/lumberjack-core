<?php

namespace Rareloop\Lumberjack\Bootstrappers;

use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Helpers;

class RegisterAliases
{
    public function bootstrap(Application $app)
    {
        $config = $app->get('config');

        collect($config->get('app.aliases', []))
            ->map(fn($realClassname) => Helpers::invoke($realClassname))
            ->filter()
            ->each(fn($realClassname, $alias) => class_alias($realClassname, $alias));
    }
}
