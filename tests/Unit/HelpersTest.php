<?php

namespace Rareloop\Lumberjack\Test;

use Blast\Facades\FacadeFactory;
use Hamcrest\Arrays\IsArrayContainingKeyValuePair;
use PHPUnit\Framework\TestCase;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Config;
use Rareloop\Lumberjack\Exceptions\Handler;
use Rareloop\Lumberjack\Exceptions\HandlerInterface;
use Rareloop\Lumberjack\Helpers;
use Rareloop\Lumberjack\Http\Responses\TimberResponse;
use Rareloop\Router\Router;
use Timber\Timber;
use Zend\Diactoros\Response\RedirectResponse;

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
        $router->get('test/route', function () {})->name('test.route');
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
        $router->get('test/{name}', function ($name) {})->name('test.route');
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
        $handler = \Mockery::mock(TestExceptionHandler::class.'[report]', [$app]);
        $handler->shouldReceive('report')->with($exception)->once();

        $app->bind(HandlerInterface::class, function () use ($handler) {
            return $handler;
        });

        Helpers::report($exception);
    }
}

class TestExceptionHandler extends Handler
{

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
