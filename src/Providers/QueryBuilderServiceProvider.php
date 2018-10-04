<?php

namespace Rareloop\Lumberjack\Providers;

use Rareloop\Lumberjack\Providers\ServiceProvider;
use Rareloop\Lumberjack\Contracts\QueryBuilder as QueryBuilderContract;
use Rareloop\Lumberjack\QueryBuilder;

class QueryBuilderServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(QueryBuilderContract::class, QueryBuilder::class);
    }
}
