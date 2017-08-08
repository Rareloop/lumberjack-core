<?php

namespace Rareloop\Lumberjack\Http;

use DI\ContainerBuilder;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Bootstrappers\BootProviders;
use Rareloop\Lumberjack\Bootstrappers\LoadConfiguration;
use Rareloop\Lumberjack\Bootstrappers\RegisterFacades;
use Rareloop\Lumberjack\Bootstrappers\RegisterProviders;

class Kernal
{
    private $app;

    protected $bootstrappers = [
        LoadConfiguration::class,
        RegisterFacades::class,
        RegisterProviders::class,
        BootProviders::class,
    ];

    public function __construct(Application $app)
    {
        $this->app = $app;

        add_action('after_theme_setup', [$this, 'bootstrap']);
    }

    public function bootstrap()
    {
        $this->app->bootstrapWith($this->bootstrappers());
    }

    protected function bootstrappers()
    {
        return $this->bootstrappers;
    }
}
