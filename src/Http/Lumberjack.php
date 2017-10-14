<?php

namespace Rareloop\Lumberjack\Http;

use DI\ContainerBuilder;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Bootstrappers\BootProviders;
use Rareloop\Lumberjack\Bootstrappers\LoadConfiguration;
use Rareloop\Lumberjack\Bootstrappers\RegisterExceptionHandler;
use Rareloop\Lumberjack\Bootstrappers\RegisterFacades;
use Rareloop\Lumberjack\Bootstrappers\RegisterProviders;

class Lumberjack
{
    private $app;

    protected $bootstrappers = [
        LoadConfiguration::class,
        RegisterExceptionHandler::class,
        RegisterFacades::class,
        RegisterProviders::class,
        BootProviders::class,
    ];

    public function __construct(Application $app)
    {
        $this->app = $app;
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
