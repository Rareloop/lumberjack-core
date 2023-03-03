<?php

namespace Rareloop\Lumberjack\Test\Facades;

use Blast\Facades\FacadeFactory;
use PHPUnit\Framework\TestCase;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Facades\MiddlewareAliases;
use Rareloop\Lumberjack\Http\MiddlewareAliasStore;

class MiddlewareAliasesTest extends TestCase
{
    /** @test */
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
