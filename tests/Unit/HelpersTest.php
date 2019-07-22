<?php

namespace Rareloop\Lumberjack\Test;

use Timber\Timber;
use Rareloop\Router\Router;
use PHPUnit\Framework\TestCase;
use Rareloop\Lumberjack\Config;
use Blast\Facades\FacadeFactory;
use Rareloop\Lumberjack\Helpers;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Facades\Session;
use Rareloop\Lumberjack\Exceptions\Handler;
use Rareloop\Lumberjack\Http\ServerRequest;
use Rareloop\Lumberjack\Session\SessionManager;
use Hamcrest\Arrays\IsArrayContainingKeyValuePair;
use Rareloop\Lumberjack\Exceptions\HandlerInterface;
use Rareloop\Lumberjack\Http\Responses\TimberResponse;
use Rareloop\Lumberjack\Http\Responses\RedirectResponse;
use Monolog\Logger;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class HelpersTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    /** @test */
    public function can_retrieve_the_container_instance()
    {
        $app = new Application;

        $this->assertSame($app, Helpers::app());
    }

    /** @test */
    public function can_resolve_something_from_the_container()
    {
        $app = new Application;
        $app->bind('test', 123);

        $this->assertSame(123, Helpers::app('test'));
    }

    /** @test */
    public function can_retrieve_a_config_value()
    {
        $app = new Application;
        FacadeFactory::setContainer($app);

        $config = new Config();
        $config->set('app.environment', 'production');
        $app->bind('config', $config);

        $this->assertSame('production', Helpers::config('app.environment'));
    }

    /** @test */
    public function can_retrieve_a_default_when_no_config_value_is_set()
    {
        $app = new Application;
        FacadeFactory::setContainer($app);

        $config = new Config();
        $app->bind('config', $config);

        $this->assertSame('production', Helpers::config('app.environment', 'production'));
    }

    /** @test */
    public function can_set_a_config_value_when_array_passed_to_config_helper()
    {
        $app = new Application;
        FacadeFactory::setContainer($app);
        $config = new Config();
        $app->bind('config', $config);

        Helpers::config([
            'app.environment' => 'production',
            'app.debug' => true,
        ]);

        $this->assertSame('production', $config->get('app.environment'));
        $this->assertSame(true, $config->get('app.debug'));
    }

    /** @test */
    public function can_get_a_timber_response()
    {
        $timber = \Mockery::mock('alias:' . Timber::class);
        $timber->shouldReceive('compile')
            ->with('template.twig', IsArrayContainingKeyValuePair::hasKeyValuePair('foo', 'bar'))
            ->once()
            ->andReturn('testing123');

        $view = Helpers::view('template.twig', [
            'foo' => 'bar',
        ]);

        $this->assertInstanceOf(TimberResponse::class, $view);
        $this->assertSame('testing123', $view->getBody()->getContents());
        $this->assertSame(200, $view->getStatusCode());
    }

    /** @test */
    public function can_get_a_timber_response_with_a_specific_status_code()
    {
        $timber = \Mockery::mock('alias:' . Timber::class);
        $timber->shouldReceive('compile')
            ->once()
            ->andReturn('testing123');

        $view = Helpers::view('template.twig', [], 404);

        $this->assertSame(404, $view->getStatusCode());
    }

    /** @test */
    public function can_get_a_timber_response_with_specific_headers()
    {
        $timber = \Mockery::mock('alias:' . Timber::class);
        $timber->shouldReceive('compile')
            ->once()
            ->andReturn('testing123');

        $view = Helpers::view('template.twig', [], 200, [
            'X-Test-Header' => 'testing',
        ]);

        $headers = $view->getHeaders();

        $this->assertNotNull($headers['X-Test-Header']);
        $this->assertSame('testing', $headers['X-Test-Header'][0]);
    }

    /** @test */
    public function can_get_a_url_for_a_named_route()
    {
        $app = new Application;
        FacadeFactory::setContainer($app);
        $router = new Router;
        $router->get('test/route', function () { })->name('test.route');
        $app->bind('router', $router);

        $url = Helpers::route('test.route');

        $this->assertSame('test/route', trim($url, '/'));
    }

    /** @test */
    public function can_get_a_url_for_a_named_route_with_params()
    {
        $app = new Application;
        FacadeFactory::setContainer($app);
        $router = new Router;
        $router->get('test/{name}', function ($name) { })->name('test.route');
        $app->bind('router', $router);

        $url = Helpers::route('test.route', [
            'name' => 'route',
        ]);

        $this->assertSame('test/route', trim($url, '/'));
    }

    /** @test */
    public function can_get_a_redirect_response()
    {
        $response = Helpers::redirect('/new/url');
        $headers = $response->getHeaders();

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertNotNull($headers['location']);
        $this->assertSame('/new/url', $headers['location'][0]);
    }

    /** @test */
    public function can_get_a_redirect_response_with_custom_status_code()
    {
        $response = Helpers::redirect('/new/url', 301);

        $this->assertSame(301, $response->getStatusCode());
    }

    /** @test */
    public function can_get_a_redirect_response_with_custom_headers()
    {
        $response = Helpers::redirect('/new/url', 301, [
            'X-Test-Header' => 'testing',
        ]);

        $headers = $response->getHeaders();

        $this->assertNotNull($headers['X-Test-Header']);
        $this->assertSame('testing', $headers['X-Test-Header'][0]);
    }

    /** @test */
    public function can_report_an_exception()
    {
        $app = new Application;
        $exception = new \Exception('Testing 123');
        $handler = \Mockery::mock(TestExceptionHandler::class . '[report]', [$app]);
        $handler->shouldReceive('report')->with($exception)->once();

        $app->bind(HandlerInterface::class, function () use ($handler) {
            return $handler;
        });

        Helpers::report($exception);
    }

    /** @test */
    public function can_access_an_item_in_the_session_by_key()
    {
        $app = new Application;
        FacadeFactory::setContainer($app);

        $store = new SessionManager($app);
        $app->bind('session', $store);

        Session::put('test', 123);

        $this->assertSame(123, Helpers::session('test'));
    }

    /** @test */
    public function can_access_an_item_in_the_session_by_key_with_default()
    {
        $app = new Application;
        FacadeFactory::setContainer($app);

        $store = new SessionManager($app);
        $app->bind('session', $store);

        $this->assertSame(123, Helpers::session('test', 123));
    }

    /** @test */
    public function can_add_an_item_in_the_session()
    {
        $app = new Application;
        FacadeFactory::setContainer($app);

        $store = new SessionManager($app);
        $app->bind('session', $store);

        Helpers::session(['test' => 123]);

        $this->assertSame(123, Helpers::session('test'));
    }

    /** @test */
    public function can_add_multiple_items_to_the_session()
    {
        $app = new Application;
        FacadeFactory::setContainer($app);

        $store = new SessionManager($app);
        $app->bind('session', $store);

        Helpers::session(['test' => 123, 'foo' => 'bar']);

        $this->assertSame(123, Helpers::session('test'));
        $this->assertSame('bar', Helpers::session('foo'));
    }

    /** @test */
    public function can_resolve_the_session_manager()
    {
        $app = new Application;
        FacadeFactory::setContainer($app);

        $store = new SessionManager($app);
        $app->bind('session', $store);

        $this->assertSame($store, Helpers::session());
    }

    /** @test */
    public function can_redirect_back()
    {
        $app = new Application;
        FacadeFactory::setContainer($app);
        $store = new SessionManager($app);
        $app->bind('session', $store);
        $store->setPreviousUrl('http://domain.com/previous/url');

        $response = Helpers::back();

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('http://domain.com/previous/url', $response->getHeader('Location')[0]);
    }

    /** @test */
    public function can_get_server_request()
    {
        $app = new Application;
        FacadeFactory::setContainer($app);

        $request = new ServerRequest([], [], '/test/123', 'GET');
        $app->bind('request', $request);

        $this->assertSame($request, Helpers::request());
    }

    /** @test */
    public function can_get_logger()
    {
        $app = new Application;
        FacadeFactory::setContainer($app);

        $logger = new Logger('app');
        $app->bind('logger', $logger);

        $newLogger = Helpers::logger();

        $this->assertInstanceOf(Logger::class, $newLogger);
        $this->assertSame($logger, $newLogger);
    }

    /** @test */
    public function can_write_debug_log()
    {
        $app = new Application;
        FacadeFactory::setContainer($app);

        $logger = \Mockery::mock(Logger::class)->makePartial();
        $logger->shouldReceive('debug')->with('Example message', [])->once();

        $app->bind('logger', $logger);

        Helpers::logger('Example message');
    }

    /** @test */
    public function can_write_debug_log_with_context()
    {
        $app = new Application;
        FacadeFactory::setContainer($app);

        $logger = \Mockery::mock(Logger::class)->makePartial();
        $logger->shouldReceive('debug')->with('Example message', [
            'test' => 123,
        ])->once();

        $app->bind('logger', $logger);

        Helpers::logger('Example message', [
            'test' => 123,
        ]);
    }
}

class TestExceptionHandler extends Handler
{
    // ...
}

class RequiresConstructorParams
{
    public $param1;
    public $param2;

    public function __construct($param1, $param2)
    {
        $this->param1 = $param1;
        $this->param2 = $param2;
    }
}
