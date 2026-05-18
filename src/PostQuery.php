<?php

namespace Rareloop\Lumberjack;

use Spatie\Macroable\Macroable;
use Timber\PostQuery as TimberPostQuery;

class PostQuery extends TimberPostQuery
{
    use Macroable;
}
