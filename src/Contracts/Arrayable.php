<?php

namespace Rareloop\Lumberjack\Contracts;

interface Arrayable
{
    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray() : array;
}
