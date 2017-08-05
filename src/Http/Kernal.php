<?php

namespace Rareloop\Lumberjack\Http;

use DI\ContainerBuilder;
use Rareloop\Lumberjack\Application;

abstract class Kernal
{
    private $app;

    private $bootstrappers = [
        // LoadConfig
        // RegisterServiceProviders
        // BootProviders
    ];

    public function __construct(Application $app)
    {
        $this->app = $app;

        add_action('after_theme_setup', [$this, 'bootstrap']);

        // $this->addBootstrappersToContainer()
    }

    public function bootstrap()
    {
        // $this->app->bootstrapWith($this->bootstrappers);
    }
}
