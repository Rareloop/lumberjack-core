<?php

namespace Rareloop\Lumberjack\Test\Http;

use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Rareloop\Lumberjack\Test\TestCase;
use Rareloop\Lumberjack\Http\Router;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\Attributes\PreserveGlobalState;

/**
 * Ensure all class_alias calls are reset each time
 */
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class RouterTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    #[Test]
    public function controller_has_namespace_added(): void
    {
        class_alias(RouterTestController::class, 'App\Http\Controllers\MyController');
        $router = new Router();

        $route = $router->get('/test/123', 'MyController@test');

        $this->assertSame('App\Http\Controllers\MyController@test', $route->getActionName());
    }

    #[Test]
    public function controller_does_not_have_namespace_added_when_it_already_exists(): void
    {
        $router = new Router();

        $route = $router->get('/test/123', RouterTestController::class . '@test');

        $this->assertSame(RouterTestController::class . '@test', $route->getActionName());
    }

    #[Test]
    public function controller_does_not_have_namespace_added_when_it_is_callable(): void
    {
        $router = new Router();
        $controller = new RouterTestController();

        $route = $router->get('/test/123', [$controller, 'test']);

        $this->assertSame(RouterTestController::class . '@test', $route->getActionName());
    }

    #[Test]
    public function controller_does_not_have_namespace_added_when_it_is_closure(): void
    {
        $router = new Router();
        $controller = new RouterTestController();

        $route = $router->get('/test/123', function () {
        });

        $this->assertSame('Closure', $route->getActionName());
    }

    #[Test]
    public function can_extend_post_behaviour_with_macros(): void
    {
        Router::macro('testFunctionAddedByMacro', function () {
            return 'abc123';
        });

        $queryBuilder = new Router();

        $this->assertSame('abc123', $queryBuilder->testFunctionAddedByMacro());
        $this->assertSame('abc123', Router::testFunctionAddedByMacro());
    }

    #[Test]
    public function can_extend_post_behaviour_with_mixin(): void
    {
        Router::mixin(new RouterMixin());

        $queryBuilder = new Router();

        $this->assertSame('abc123', $queryBuilder->testFunctionAddedByMixin());
    }
}

class RouterMixin
{
    public function testFunctionAddedByMixin()
    {
        return function () {
            return 'abc123';
        };
    }
}

class RouterTestController
{
    public function test()
    {
    }
}
