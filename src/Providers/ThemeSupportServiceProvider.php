<?php

namespace Rareloop\Lumberjack\Providers;

use Rareloop\Lumberjack\Config;

class ThemeSupportServiceProvider extends ServiceProvider
{
    public function boot(Config $config)
    {
        $themeSupport = $config->get('app.themeSupport', []);
        foreach ($themeSupport as $key => $value) {
            if (is_numeric($key)) {
                add_theme_support($value);
            } else {
                add_theme_support($key, $value);
            }
        }
    }
}
