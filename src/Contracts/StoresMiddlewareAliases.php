<?php

namespace Rareloop\Lumberjack\Contracts;

interface StoresMiddlewareAliases
{
    public function set(string $name, $middleware);
    public function get(string $name);
    public function has(string $name);
}
