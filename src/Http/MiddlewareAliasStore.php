<?php

namespace Rareloop\Lumberjack\Http;

use Closure;
use Psr\Container\ContainerInterface;
use Rareloop\Lumberjack\Contracts\MiddlewareAliases;

class MiddlewareAliasStore implements MiddlewareAliases
{
    protected $aliases = [];

    public function set(string $name, $middleware)
    {
        $this->aliases[$name] = $middleware;
    }

    public function get(string $name)
    {
        list($name, $params) = $this->parseName($name);

        $middleware = $this->aliases[$name];

        if ($middleware instanceof Closure) {
            $middleware = $middleware(...$params);
        }

        if (is_string($middleware) && class_exists($middleware)) {
            $middleware = new $middleware(...$params);
        }

        return $middleware;
    }

    protected function parseName($name) : array
    {
        list($name, $params) = array_pad(explode(':', $name), 2, '');

        $params = explode(',', $params);

        return [$name, $params];
    }

    public function has(string $name) : bool
    {
        list($name, $params) = $this->parseName($name);

        return isset($this->aliases[$name]);
    }
}
