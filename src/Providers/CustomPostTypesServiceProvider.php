<?php

namespace Rareloop\Lumberjack\Providers;

use Rareloop\Lumberjack\Config;
use Rareloop\Lumberjack\Helpers;

class CustomPostTypesServiceProvider extends ServiceProvider
{
    public function boot(Config $config)
    {
        collect($config->get('posttypes.register', []))
            ->map(fn($postType) => Helpers::invoke($postType))
            ->filter()
            ->each(fn($postType) => $postType::register());
    }
}
