<?php

namespace Rareloop\Lumberjack\Providers;

use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Config;

class MenusServiceProvider extends ServiceProvider
{
    public function boot(Config $config)
    {
        add_theme_support('menus');

        register_nav_menus($config->get('menus.menus', []));
    }
}
