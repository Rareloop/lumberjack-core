<?php

namespace Rareloop\Lumberjack\Providers;

use Rareloop\Lumberjack\Config;
use Rareloop\Lumberjack\Helpers;

class ThemeSupportServiceProvider extends ServiceProvider
{
    public function boot(Config $config)
    {
        collect($config->get('app.themeSupport', []))
            ->map(fn($value) => Helpers::invoke($value))
            ->filter()
            ->map(fn($value, $key) => is_numeric($key) ? [$value] : [$key, $value])
            ->each(fn($args) => add_theme_support(...$args));
    }
}
