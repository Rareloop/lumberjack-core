<?php

namespace Rareloop\Lumberjack\Providers;

use Rareloop\Lumberjack\Config;
use Rareloop\Lumberjack\Helpers;

class ThemeSupportServiceProvider extends ServiceProvider
{
    public function boot(Config $config)
    {
        foreach ($config->get('app.themeSupport', []) as $key => $value) {
            if (is_numeric($key)) {
                add_theme_support(Helpers::invoke($value));
            } else {
                add_theme_support($key, Helpers::invoke($value));
            }
        }
    }
}
