<?php

namespace Rareloop\Lumberjack\Providers;

use Rareloop\Lumberjack\Config;
use Rareloop\Lumberjack\Helpers;

class MenusServiceProvider extends ServiceProvider
{
    public function boot(Config $config)
    {
        add_theme_support('menus');

        $menus = collect($config->get('menus.menus', []))
            ->map(fn($menu) => Helpers::invoke($menu))
            ->filter()
            ->all();

        if (count($menus)) {
            register_nav_menus($menus);
        }
    }
}
