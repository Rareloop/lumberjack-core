<?php

namespace Rareloop\Lumberjack\Facades;

use Rareloop\Lumberjack\FacadeManager;

abstract class AbstractFacade
{
    public static function __callStatic($name, array $arguments = [])
    {
        return FacadeManager::create(static::accessor(), $name, $arguments);
    }

    public static function __instance()
    {
        return FacadeManager::getContainer()->get(static::accessor());
    }

    abstract protected static function accessor();
}
