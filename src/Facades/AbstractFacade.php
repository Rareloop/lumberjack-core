<?php

namespace Rareloop\Lumberjack\Facades;

use Rareloop\Lumberjack\FacadeFactory;

abstract class AbstractFacade
{
    public static function __callStatic($name, array $arguments = [])
    {
        return FacadeFactory::create(static::accessor(), $name, $arguments);
    }

    public static function __instance()
    {
        return FacadeFactory::getContainer()->get(static::accessor());
    }

    abstract protected static function accessor();
}
