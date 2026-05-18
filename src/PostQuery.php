<?php

namespace Rareloop\Lumberjack;

use Illuminate\Support\Collection;
use Spatie\Macroable\Macroable;
use Timber\PostQuery as TimberPostQuery;

class PostQuery extends TimberPostQuery
{
    use Macroable;

    /**
     * Get the posts in this query as a Collection.
     *
     * @return \Illuminate\Support\Collection
     */
    public function toCollection(): Collection
    {
        return new Collection($this->getArrayCopy());
    }
}
