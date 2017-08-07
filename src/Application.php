<?php

namespace Rareloop\Lumberjack;

use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use Rareloop\Lumberjack\ServiceProvider;

class Application implements ContainerInterface
{
    private $container;
    private $loadedProviders = [];
    private $booted = false;

    public function __construct()
    {
        $this->container = ContainerBuilder::buildDevContainer();

        $this->bind(Application::class, $this);
    }

    public function bind($key, $value)
    {
        if (is_string($value) && class_exists($value)) {
            $value = \DI\Object($value);
        }

        $this->container->set($key, $value);
    }

    public function make($key)
    {
        return $this->container->make($key);
    }

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @throws NotFoundExceptionInterface  No entry was found for **this** identifier.
     * @throws ContainerExceptionInterface Error while retrieving the entry.
     *
     * @return mixed Entry.
     */
    public function get($id)
    {
        return $this->container->get($id);
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * `has($id)` returning true does not mean that `get($id)` will not throw an exception.
     * It does however mean that `get($id)` will not throw a `NotFoundExceptionInterface`.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return bool
     */
    public function has($id)
    {
        return $this->container->has($id);
    }

    public function register($provider)
    {
        if (method_exists($provider, 'register')) {
            $provider->register($this);
        }

        $this->loadedProviders[] = $provider;

        if ($this->booted) {
            $this->bootProvider($provider);
        }
    }

    public function getLoadedProviders()
    {
        return $this->loadedProviders;
    }

    public function boot()
    {
        if ($this->booted) {
            return;
        }

        foreach ($this->loadedProviders as $provider) {
            $this->bootProvider($provider);
        }

        $this->booted = true;
    }

    private function bootProvider($provider)
    {
        if (method_exists($provider, 'boot')) {
            $this->container->call([$provider, 'boot']);
        }
    }

    public function isBooted()
    {
        return $this->booted;
    }
}
