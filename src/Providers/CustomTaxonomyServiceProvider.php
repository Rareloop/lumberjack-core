<?php

namespace Rareloop\Lumberjack\Providers;

use Rareloop\Lumberjack\Config;

class CustomTaxonomyServiceProvider extends ServiceProvider
{
    public function boot(Config $config)
    {
        $taxonomiesToRegister = $config->get('taxonomies.register');

        foreach ($taxonomiesToRegister as $taxonomy) {
            $taxonomy::register();
        }
    }
}
