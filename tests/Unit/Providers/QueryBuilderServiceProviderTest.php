<?php

namespace Rareloop\Lumberjack\Test\Providers;

use PHPUnit\Framework\Attributes\Test;
use Rareloop\Lumberjack\Test\TestCase;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Config;
use Rareloop\Lumberjack\Http\Lumberjack;
use Rareloop\Lumberjack\Contracts\QueryBuilder as QueryBuilderContract;
use Rareloop\Lumberjack\Providers\QueryBuilderServiceProvider;
use Rareloop\Lumberjack\QueryBuilder;

class QueryBuilderServiceProviderTest extends TestCase
{
    #[Test]
    public function query_builder_is_registered_into_container(): void
    {
        $app = new Application();
        $provider = new QueryBuilderServiceProvider($app);

        $provider->register();

        $this->assertTrue($app->has(QueryBuilderContract::class));
        $this->assertInstanceOf(QueryBuilder::class, $app->get(QueryBuilderContract::class));
    }
}
