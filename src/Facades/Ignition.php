<?php

namespace Rareloop\Lumberjack\Facades;

use Spatie\Ignition\Ignition as SpatieIgnition;

class Ignition extends AbstractFacade
{
    /**
     * The name of the binding in the container
     *
     * @return string
     */
    protected static function accessor()
    {
        return SpatieIgnition::class;
    }
}
