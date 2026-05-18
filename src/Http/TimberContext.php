<?php

namespace Rareloop\Lumberjack\Http;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Tappable;

class TimberContext extends Collection
{
    use Conditionable;
    use Tappable;

    /**
     * Set a value in the context using dot-notation.
     *
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function set(string $key, mixed $value = null): self
    {
        Arr::set($this->items, $key, $value);

        return $this;
    }

    /**
     * Get an item from the context using dot-notation.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key, $default = null): mixed
    {
        return Arr::get($this->items, $key, $default);
    }

    /**
     * Determine if an item exists in the context using dot-notation.
     *
     * @param string $key
     * @return bool
     */
    public function has($key): bool
    {
        return Arr::has($this->items, $key);
    }
}
