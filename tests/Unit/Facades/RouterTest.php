<?php

namespace Rareloop\Lumberjack\Test\Facades;

use Illuminate\Support\Facades\Facade;
use PHPUnit\Framework\TestCase;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Facades\Router as RouterFacade;
use Rareloop\Lumberjack\Http\Router;

class RouterTest extends TestCase
{
    /** @test */
    public function test_facade()
    {
        $app = new Application();
        Facade::setFacadeApplication($app);

        $router = new Router();
        $app->bind('router', $router);

        $this->assertInstanceOf(Router::class, RouterFacade::getFacadeRoot());
        $this->assertSame($router, RouterFacade::getFacadeRoot());
    }
}
