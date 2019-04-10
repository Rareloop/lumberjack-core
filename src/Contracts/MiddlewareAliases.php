<?php

namespace Rareloop\Lumberjack\Contracts;

interface MiddlewareAliases
{
    public function set(string $name, $middleware);
    public function get(string $name);
    public function has(string $name) : bool;
}
