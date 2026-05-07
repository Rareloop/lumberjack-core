<?php

namespace Rareloop\Lumberjack\Bootstrappers;

use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Autodiscovery\AutodiscoveredPackages;

class RegisterAliases
{
    public function bootstrap(Application $app)
    {
        $config = $app->get('config');
        $manifest = $app->get(AutodiscoveredPackages::class);

        $aliases = array_merge(
            $manifest->aliases(),
            $config->get('app.aliases', [])
        );

        foreach ($aliases as $alias => $realClassname) {
            class_alias($realClassname, $alias);
        }
    }
}
