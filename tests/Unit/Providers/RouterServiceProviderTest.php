<?php

namespace Rareloop\Lumberjack\Test\Providers;

use Brain\Monkey;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Contracts\MiddlewareAliases;
use Rareloop\Lumberjack\Http\Lumberjack;
use Rareloop\Lumberjack\Http\Router;
use Rareloop\Lumberjack\Providers\RouterServiceProvider;
use Rareloop\Lumberjack\Test\Unit\BrainMonkeyPHPUnitIntegration;
use Rareloop\Router\MiddlewareResolver;
use Zend\Diactoros\Request;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\TextResponse;
use Zend\Diactoros\ServerRequest;

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
    public function middleware_alias_objects_are_configured()
    {
        Functions\expect('is_admin')->once()->andReturn(false);

        $this->setSiteUrl('http://example.com/sub-path/');
        $app = new Application(__DIR__.'/../');
        $lumberjack = new Lumberjack($app);

        $app->register(new RouterServiceProvider($app));
        $lumberjack->bootstrap();

        $this->assertTrue($app->has('middleware-alias-store'));
        $this->assertSame($app->get('middleware-alias-store'), $app->get(MiddlewareAliases::class));

        $this->assertTrue($app->has('middleware-resolver'));
        $this->assertSame($app->get('middleware-resolver'), $app->get(MiddlewareResolver::class));
    }

    /** @test */
    public function configured_router_can_resolve_middleware_aliases()
    {
        Functions\expect('is_admin')->once()->andReturn(false);

        $this->setSiteUrl('http://example.com/');
        $app = new Application(__DIR__.'/../');
        $lumberjack = new Lumberjack($app);

        $app->register(new RouterServiceProvider($app));
        $lumberjack->bootstrap();

        $router = $app->get(Router::class);
        $store = $app->get(MiddlewareAliases::class);
        $store->set('middleware-key', new RSPAddHeaderMiddleware('X-Key', 'abc'));
        $request = new ServerRequest([], [], '/test/123', 'GET');

        $router->get('/test/123', function () {})->middleware('middleware-key');
        $response = $router->match($request);

        $this->assertTrue($response->hasHeader('X-Key'));
        $this->assertSame('abc', $response->getHeader('X-Key')[0]);
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

    /** @test */
    public function lumberjack_router_response_filter_is_fired_when_request_is_processed()
    {
        Functions\expect('is_admin')->once()->andReturn(false);
        $this->setSiteUrl('http://example.com/sub-path/');

        $request = new ServerRequest([], [], '/test/123', 'GET');
        $response = new HtmlResponse('testing 123');

        $app = Mockery::mock(Application::class.'[shutdown]', [__DIR__.'/..']);
        $app->shouldReceive('shutdown')->times(1)->with($response);
        $lumberjack = new Lumberjack($app);
        $provider = new RouterServiceProvider($app);

        $app->register($provider);
        $lumberjack->bootstrap();

        $router = Mockery::mock(Router::class.'[match]', $app);
        $router->shouldReceive('match')->andReturn($response)->once();

        $app->bind('router', $router);

        Filters\expectApplied('lumberjack_router_response')
            ->once()
            ->with($response, $request);

        $provider->processRequest($request);
    }

    /** @test */
    public function matched_request_will_mark_request_handled_in_app()
    {
        Functions\expect('is_admin')->once()->andReturn(false);

        $this->setSiteUrl('http://example.com/sub-path/');
        $response = new TextResponse('Testing 123', 200);
        $app = Mockery::mock(Application::class.'[shutdown]', [__DIR__.'/..']);
        $app->shouldReceive('shutdown');

        $lumberjack = new Lumberjack($app);
        $provider = new RouterServiceProvider($app);

        $app->register($provider);
        $lumberjack->bootstrap();

        $router = Mockery::mock(Router::class.'[match]', $app);
        $router->shouldReceive('match')->andReturn($response)->once();

        $app->bind('router', $router);

        $provider->processRequest(new ServerRequest([], [], '/test/123', 'GET'));

        $this->assertTrue($app->hasRequestBeenHandled());
    }

    /** @test */
    public function unmatched_request_will_not_mark_request_handled_in_app()
    {
        Functions\expect('is_admin')->once()->andReturn(false);

        $this->setSiteUrl('http://example.com/sub-path/');
        $response = new TextResponse('Testing 123', 404);
        $app = Mockery::mock(Application::class.'[shutdown]', [__DIR__.'/..']);
        $app->shouldReceive('shutdown');

        $lumberjack = new Lumberjack($app);
        $provider = new RouterServiceProvider($app);

        $app->register($provider);
        $lumberjack->bootstrap();

        $router = Mockery::mock(Router::class.'[match]', $app);
        $router->shouldReceive('match')->andReturn($response)->once();

        $app->bind('router', $router);

        $provider->processRequest(new ServerRequest([], [], '/test/123', 'GET'));

        $this->assertFalse($app->hasRequestBeenHandled());
    }

    private function setSiteUrl($url) {
        Functions\when('get_bloginfo')->alias(function ($key) use ($url) {
            if ($key === 'url') {
                return $url;
            }
        });
    }
}

class RSPAddHeaderMiddleware implements MiddlewareInterface
{
    private $key;
    private $value;

    public function __construct($key, $value)
    {
        $this->key = $key;
        $this->value = $value;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        $response = $handler->handle($request);

        return $response->withHeader($this->key, $this->value);
    }
}
