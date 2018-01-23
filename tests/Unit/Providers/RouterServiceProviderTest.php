<?php

namespace Rareloop\Lumberjack\Test\Providers;

use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Http\Lumberjack;
use Rareloop\Lumberjack\Providers\RouterServiceProvider;
use Rareloop\Lumberjack\Test\Unit\BrainMonkeyPHPUnitIntegration;
use Rareloop\Router\Router;
use Zend\Diactoros\Request;
use Zend\Diactoros\Response\TextResponse;
use Zend\Diactoros\ServerRequest;
use \Mockery;

class RouterServiceProviderTest extends TestCase
{
    use BrainMonkeyPHPUnitIntegration;

    /** @test */
    public function router_object_is_configured()
    {
        Functions\expect('is_admin')->once()->andReturn(false);

        $this->setSiteUrl('http://example.com/sub-path/');
        $app = new Application(__DIR__.'/../');
        $lumberjack = new Lumberjack($app);

        $app->register(new RouterServiceProvider($app));
        $lumberjack->bootstrap();

        $this->assertTrue($app->has('router'));
        $this->assertSame($app->get('router'), $app->get(Router::class));
    }

    /** @test */
    public function basedir_is_set_from_wordpress_config()
    {
        Functions\expect('is_admin')->once()->andReturn(false);

        $this->setSiteUrl('http://example.com/sub-path/');
        $request = new ServerRequest([], [], '/sub-path/test/123', 'GET');

        $app = new Application(__DIR__.'/../');
        $lumberjack = new Lumberjack($app);

        $app->register(new RouterServiceProvider($app));
        $lumberjack->bootstrap();

        $router = $app->get('router');
        $router->get('/test/123', function () {
            return 'abc123';
        });

        $response = $router->match($request);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('abc123', $response->getBody()->__toString());
    }

    /** @test */
    public function wp_loaded_action_is_bound()
    {
        Functions\expect('is_admin')->once()->andReturn(false);

        $this->setSiteUrl('http://example.com/sub-path/');
        $app = new Application(__DIR__.'/../');
        $lumberjack = new Lumberjack($app);
        $provider = new RouterServiceProvider($app);

        $app->register($provider);
        $lumberjack->bootstrap();

        $this->assertTrue(has_action('wp_loaded', 'function ()'));
    }

    /** @test */
    public function request_object_is_bound_into_the_container()
    {
        Functions\expect('is_admin')->once()->andReturn(false);

        $this->setSiteUrl('http://example.com/sub-path/');
        $request = new ServerRequest([], [], '/test/123', 'GET');
        $app = new Application(__DIR__.'/../');
        $lumberjack = new Lumberjack($app);
        $provider = new RouterServiceProvider($app);

        $app->register($provider);
        $lumberjack->bootstrap();

        $provider->processRequest($request);

        $this->assertSame($request, $app->get('request'));
    }

    /** @test */
    public function unmatched_request_will_not_call_app_shutdown_method()
    {
        Functions\expect('is_admin')->once()->andReturn(false);

        $this->setSiteUrl('http://example.com/sub-path/');
        $response = new TextResponse('Testing 123', 404);
        $app = Mockery::mock(Application::class.'[shutdown]', [__DIR__.'/..']);
        $app->shouldReceive('shutdown')->times(0)->with($response);

        $lumberjack = new Lumberjack($app);
        $provider = new RouterServiceProvider($app);

        $app->register($provider);
        $lumberjack->bootstrap();

        $router = Mockery::mock(Router::class.'[match]', $app);
        $router->shouldReceive('match')->andReturn($response)->once();

        $app->bind('router', $router);

        $provider->processRequest(new ServerRequest([], [], '/test/123', 'GET'));
    }

    /** @test */
    public function matched_request_will_call_app_shutdown_method()
    {
        Functions\expect('is_admin')->once()->andReturn(false);

        $this->setSiteUrl('http://example.com/sub-path/');
        $response = new TextResponse('Testing 123', 200);
        $app = Mockery::mock(Application::class.'[shutdown]', [__DIR__.'/..']);
        $app->shouldReceive('shutdown')->times(1)->with($response);

        $lumberjack = new Lumberjack($app);
        $provider = new RouterServiceProvider($app);

        $app->register($provider);
        $lumberjack->bootstrap();

        $router = Mockery::mock(Router::class.'[match]', $app);
        $router->shouldReceive('match')->andReturn($response)->once();

        $app->bind('router', $router);

        $provider->processRequest(new ServerRequest([], [], '/test/123', 'GET'));
    }

    private function setSiteUrl($url) {
        Functions\when('get_bloginfo')->alias(function ($key) use ($url) {
            if ($key === 'url') {
                return $url;
            }
        });
    }
}
