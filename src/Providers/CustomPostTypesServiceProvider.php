<?php

namespace Rareloop\Lumberjack\Providers;

use Rareloop\Lumberjack\Config;

class CustomPostTypesServiceProvider extends ServiceProvider
{
    public function boot(Config $config)
    {
        add_action('init', function () use ($config) {
            $postTypesToRegister = $config->get('posttypes.register');

            foreach ($postTypesToRegister as $postType) {
                $postType::register();
            }
        });
    }
}
