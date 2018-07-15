<?php

namespace Rareloop\Lumberjack\Test\Http;

use Mockery;
use PHPUnit\Framework\TestCase;
use Rareloop\Lumberjack\Http\Router;

/**
 * Ensure all class_alias calls are reset each time
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class RouterTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    /** @test */
    public function controller_has_namespace_added()
    {
        class_alias(RouterTestController::class, 'App\Http\Controllers\MyController');
        $router = new Router;

        $route = $router->get('/test/123', 'MyController@test');

        $this->assertSame('App\Http\Controllers\MyController@test', $route->getActionName());
    }

    /** @test */
    public function controller_does_not_have_namespace_added_when_it_already_exists()
    {
        $router = new Router;

        $route = $router->get('/test/123', RouterTestController::class . '@test');

        $this->assertSame(RouterTestController::class . '@test', $route->getActionName());
    }

    /** @test */
    public function controller_does_not_have_namespace_added_when_it_is_callable()
    {
        $router = new Router;
        $controller = new RouterTestController;

        $route = $router->get('/test/123', [$controller, 'test']);

        $this->assertSame(RouterTestController::class . '@test', $route->getActionName());
    }

    /** @test */
    public function controller_does_not_have_namespace_added_when_it_is_closure()
    {
        $router = new Router;
        $controller = new RouterTestController;

        $route = $router->get('/test/123', function () {});

        $this->assertSame('Closure', $route->getActionName());
    }
}

class RouterTestController
{
    public function test() {}
}
