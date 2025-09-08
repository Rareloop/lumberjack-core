<?php

namespace Rareloop\Lumberjack\Facades;

use Mockery;
use Rareloop\Lumberjack\FacadeManager;

abstract class Facade
{
    public static function __callStatic($name, array $arguments = [])
    {
        return FacadeManager::create(static::accessor(), $name, $arguments);
    }

    public static function __instance()
    {
        return FacadeManager::getContainer()->get(static::accessor());
    }

    abstract public static function accessor(): string;
}
