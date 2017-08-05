<?php

namespace Rareloop\Lumberjack\Http;

use DI\ContainerBuilder;

abstract class Kernal
{
    private $basePath;
    protected $container;
    private $config;
    private $router;

    public function __construct(string $basePath)
    {
        $this->basePath = $basePath;

        // Setup DI Container
        $this->container = ContainerBuilder::buildDevContainer();

        // Load Config
        $this->config = new Config($this->getConfigPath());
        $this->container->set('config', $this->config);

        // Load Router
        $this->createRouter();

        // Register Service Providers
        $this->registerServiceProviders();

        // Boot Service Providers
    }

    private function createRouter()
    {
        $this->router = new Router($this->container);
    }

    private function registerServiceProviders()
    {

    }

    public function getConfigPath() : string
    {
        return $this->basePath . '/config';
    }
}
