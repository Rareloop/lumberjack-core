<?php

namespace Rareloop\Lumberjack\Providers;

use Rareloop\Lumberjack\Config;
use Rareloop\Lumberjack\Helpers;

class CustomPostTypesServiceProvider extends ServiceProvider
{
    public function boot(Config $config)
    {
        foreach ($config->get('posttypes.register', []) as $postType) {
            Helpers::invoke($postType)::register();
        }
    }
}
