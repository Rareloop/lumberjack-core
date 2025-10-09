<?php

namespace Rareloop\Lumberjack\Http;

use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Bootstrappers\BootProviders;
use Rareloop\Lumberjack\Bootstrappers\LoadConfiguration;
use Rareloop\Lumberjack\Bootstrappers\RegisterAliases;
use Rareloop\Lumberjack\Bootstrappers\RegisterExceptionHandler;
use Rareloop\Lumberjack\Bootstrappers\RegisterFacades;
use Rareloop\Lumberjack\Bootstrappers\RegisterProviders;
use Rareloop\Lumberjack\Bootstrappers\RegisterRequestHandler;

class Lumberjack
{
    private $app;

    protected $bootstrappers = [
        LoadConfiguration::class,
        RegisterExceptionHandler::class,
        RegisterFacades::class,
        RegisterProviders::class,
        BootProviders::class,
        RegisterAliases::class,
        RegisterRequestHandler::class,
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
