<?php

namespace Rareloop\Lumberjack;

use Psr\Container\ContainerInterface;

class FacadeFactory
{
    protected static ?ContainerInterface $container = null;

    public static function getContainer()
    {
        return self::$container;
    }

    public static function setContainer(ContainerInterface $container)
    {
        self::$container = $container;
    }

    public static function create(string $accessor, $name, array $arguments = [])
    {
        return call_user_func([static::$container->get($accessor), $name], ...$arguments);
    }
}
