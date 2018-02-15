<?php

namespace Rareloop\Lumberjack;

use DI\ContainerBuilder;
use Illuminate\Support\Collection;
use Interop\Container\ContainerInterface as InteropContainerInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Rareloop\Lumberjack\ServiceProvider;
use function Http\Response\send;

class Application implements ContainerInterface, InteropContainerInterface
{
    private $container;
    private $loadedProviders = [];
    private $booted = false;
    private $basePath;

    public function __construct($basePath = false)
    {
        $this->container = ContainerBuilder::buildDevContainer();

        $this->bind(Application::class, $this);

        if ($basePath) {
            $this->setBasePath($basePath);
        }
    }

    public function setBasePath(string $basePath)
    {
        $this->basePath = $basePath;

        $this->bindPathsInContainer();
    }

    protected function bindPathsInContainer()
    {
        $this->bind('path.base', $this->basePath());
        $this->bind('path.config', $this->configPath());
    }

    public function basePath()
    {
        return $this->basePath;
    }

    public function configPath()
    {
        return $this->basePath . DIRECTORY_SEPARATOR . 'config';
    }

    public function bind($key, $value)
    {
        if (is_string($value) && class_exists($value)) {
            $value = \DI\Object($value);
        }

        $this->container->set($key, $value);
    }

    public function make($key, array $params = [])
    {
        return $this->container->make($key, $params);
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
        if ($foundProvider = $this->getProvider($provider)) {
            return $foundProvider;
        }

        if (is_string($provider)) {
            $provider = new $provider($this);
        }

        if (method_exists($provider, 'register')) {
            $provider->register();
        }

        $this->loadedProviders[] = $provider;

        if ($this->booted) {
            $this->bootProvider($provider);
        }

        return $provider;
    }

    public function getProvider($provider)
    {
        $providerClass = is_string($provider) ? $provider : get_class($provider);

        return (new Collection($this->loadedProviders))->first(function ($provider) use ($providerClass) {
            return get_class($provider) === $providerClass;
        });
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

    public function bootstrapWith(array $bootstrappers)
    {
        foreach ($bootstrappers as $bootstrapper) {
            $this->make($bootstrapper)->bootstrap($this);
        }
    }

    public function shutdown(ResponseInterface $response = null)
    {
        if ($response) {
            global $wp;
            $wp->send_headers();
            
            send($response);
        }

        die();
    }
}
