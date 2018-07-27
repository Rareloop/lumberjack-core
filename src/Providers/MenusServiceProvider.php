<?php

namespace Rareloop\Lumberjack\Providers;

use Rareloop\Lumberjack\Config;

class MenusServiceProvider extends ServiceProvider
{
    public function boot(Config $config)
    {
        add_theme_support('menus');

        $menus = $config->get('menus.menus', []);

        if (count($menus)) {
            register_nav_menus($menus);
        }
    }
}
