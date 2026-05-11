<?php

namespace Rareloop\Lumberjack\Test\Facades;

use PHPUnit\Framework\Attributes\Test;
use Rareloop\Lumberjack\Test\TestCase;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\FacadeFactory;
use Rareloop\Lumberjack\Facades\MiddlewareAliases;
use Rareloop\Lumberjack\Http\MiddlewareAliasStore;

class MiddlewareAliasesTest extends TestCase
{
    #[Test]
    public function test_facade()
    {
        $app = new Application();
        FacadeFactory::setContainer($app);

        $store = new MiddlewareAliasStore();
        $app->bind('middleware-alias-store', $store);

        $this->assertInstanceOf(MiddlewareAliasStore::class, MiddlewareAliases::__instance());
        $this->assertSame($store, MiddlewareAliases::__instance());
    }
}
