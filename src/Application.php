<?php

namespace Rareloop\Lumberjack;

use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;

class Application implements ContainerInterface
{
    private $container;

    public function __construct()
    {
        $this->container = ContainerBuilder::buildDevContainer();
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
}
