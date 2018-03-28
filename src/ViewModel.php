<?php

namespace Rareloop\Lumberjack;

use Rareloop\Lumberjack\Contracts\Arrayable;

abstract class ViewModel extends \stdClass implements Arrayable
{
    public function toArray() : array
    {
        return (array)$this;
    }
}
