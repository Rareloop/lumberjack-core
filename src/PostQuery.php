<?php

namespace Rareloop\Lumberjack;

use Illuminate\Support\Collection;
use Spatie\Macroable\Macroable;
use Timber\PostQuery as TimberPostQuery;

class PostQuery extends TimberPostQuery
{
    use Macroable;
}
