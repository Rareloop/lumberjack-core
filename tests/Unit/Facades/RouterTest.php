<?php

namespace Rareloop\Lumberjack\Test\Facades;

use PHPUnit\Framework\TestCase;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\FacadeManager;
use Rareloop\Lumberjack\Facades\Router as RouterFacade;
use Rareloop\Lumberjack\Http\Router;

class RouterTest extends TestCase
{
    /** @test */
    public function test_facade()
    {
        $app = new Application();
        FacadeManager::setContainer($app);

        $router = new Router();
        $app->bind('router', $router);

        $this->assertInstanceOf(Router::class, RouterFacade::__instance());
        $this->assertSame($router, RouterFacade::__instance());
    }
}
