<?php

namespace Rareloop\Lumberjack\Test\Providers;

use Brain\Monkey;
use PHPUnit\Framework\TestCase;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Http\Lumberjack;
use Rareloop\Lumberjack\Providers\RouterServiceProvider;
use Rareloop\Lumberjack\Test\Unit\BrainMonkeyPHPUnitIntegration;
use Rareloop\Router\Router;
use Zend\Diactoros\Response\TextResponse;
use Zend\Diactoros\ServerRequest;
use \Mockery;

class RouterServiceProviderTest extends TestCase
{
    use BrainMonkeyPHPUnitIntegration;

    /** @test */
    public function router_object_is_configured()
    {
        $app = new Application(__DIR__.'/../');
        $lumberjack = new Lumberjack($app);

        $app->register(new RouterServiceProvider);
        $lumberjack->bootstrap();

        $this->assertTrue($app->has('router'));
        $this->assertSame($app->get('router'), $app->get(Router::class));
    }

    /** @test */
    public function wp_loading_action_is_bound()
    {
        $app = new Application(__DIR__.'/../');
        $lumberjack = new Lumberjack($app);
        $provider = new RouterServiceProvider;

        $app->register($provider);
        $lumberjack->bootstrap();

        $this->assertTrue(has_action('wp_loaded', 'function ()'));
    }

    /** @test */
    public function unmatched_request_will_not_call_app_shutdown_method()
    {
        $response = new TextResponse('Testing 123', 404);
        $app = Mockery::mock(Application::class.'[shutdown]', [__DIR__.'/..']);
        $app->shouldReceive('shutdown')->times(0)->with($response);

        $lumberjack = new Lumberjack($app);
        $provider = new RouterServiceProvider;

        $app->register($provider);
        $lumberjack->bootstrap();

        $router = Mockery::mock(Router::class.'[match]', $app);
        $router->shouldReceive('match')->andReturn($response)->once();

        $app->bind('router', $router);

        $provider->processRequest(new ServerRequest([], [], '/test/123', 'GET'));
    }
}
