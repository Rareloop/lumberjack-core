<?php

namespace Rareloop\Lumberjack\Http;

use Psr\Container\ContainerInterface;
use Rareloop\Lumberjack\Contracts\StoresMiddlewareAliases;
use Rareloop\Router\ResolvesMiddleware;

class MiddlewareResolver implements ResolvesMiddleware
{
    protected $app;
    protected $store;

    public function __construct(ContainerInterface $app, StoresMiddlewareAliases $store)
    {
        $this->app = $app;
        $this->store = $store;
    }

    public function resolve($name)
    {
        if (!is_string($name)) {
            return $name;
        }

        if ($this->store->has($name)) {
            return $this->store->get($name);
        }

        return $this->app->get($name);
    }
}
