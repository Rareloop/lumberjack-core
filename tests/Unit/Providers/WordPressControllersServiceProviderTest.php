<?php

namespace Rareloop\Lumberjack\Test\Providers;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Contracts\MiddlewareAliases;
use Rareloop\Lumberjack\Http\Controller;
use Rareloop\Lumberjack\Http\Kernal;
use Rareloop\Lumberjack\Http\MiddlewareAliasStore;
use Rareloop\Lumberjack\Http\MiddlewareResolver;
use Rareloop\Lumberjack\Providers\RouterServiceProvider;
use Rareloop\Lumberjack\Providers\WordPressControllersServiceProvider;
use Rareloop\Lumberjack\Test\Unit\BrainMonkeyPHPUnitIntegration;
use Rareloop\Router\Responsable;
use Zend\Diactoros\Response\TextResponse;
use Zend\Diactoros\ServerRequest;
use \Mockery;

class WordPressControllersServiceProviderTest extends TestCase
{
    use BrainMonkeyPHPUnitIntegration;

    /** @test */
    public function template_include_filter_is_applied_on_boot()
    {
        $app = new Application(__DIR__.'/../');
        $provider = new WordPressControllersServiceProvider($app);

        $app->register($provider);
        $app->boot();

        $this->assertTrue(has_filter('template_include', [$provider, 'handleTemplateInclude']));
    }

    /** @test */
    public function handle_template_include_method_includes_the_requested_file()
    {
        $app = new Application(__DIR__.'/../');

        $this->assertNotContains(__DIR__ . '/includes/single.php', get_included_files());

        $provider = new WordPressControllersServiceProvider($app);
        $provider->handleTemplateInclude(__DIR__ . '/includes/single.php');

        $this->assertContains(__DIR__ . '/includes/single.php', get_included_files());
    }

    /** @test */
    public function handle_template_include_method_sets_details_in_container_when_controller_is_not_present()
    {
        $app = new Application(__DIR__.'/../');

        $provider = new WordPressControllersServiceProvider($app);
        $provider->handleTemplateInclude(__DIR__ . '/includes/single.php');

        $this->assertTrue($app->has('__wp-controller-miss-template'));
        $this->assertTrue($app->has('__wp-controller-miss-controller'));
        $this->assertSame('single.php', $app->get('__wp-controller-miss-template'));
        $this->assertSame('App\SingleController', $app->get('__wp-controller-miss-controller'));
    }

    /** @test */
    public function handle_template_include_method_does_not_set_details_in_container_when_controller_is_present()
    {
        $response = new TextResponse('Testing 123', 200);
        $app = Mockery::mock(Application::class.'[shutdown]', [__DIR__.'/..']);
        $app->shouldReceive('shutdown')->times(1);

        $provider = Mockery::mock(WordPressControllersServiceProvider::class.'[handleRequest]', [$app]);
        $provider->shouldReceive('handleRequest')->once()->andReturn($response);
        $provider->boot($app);

        $provider->handleTemplateInclude(__DIR__ . '/includes/single.php');

        $this->assertFalse($app->has('__wp-controller-miss-template'));
        $this->assertFalse($app->has('__wp-controller-miss-controller'));
    }

    /** @test */
    public function can_get_name_of_controller_from_template()
    {
        $app = new Application(__DIR__.'/../');
        $provider = new WordPressControllersServiceProvider($app);

        $mappings = [
            'App\\SingleController' => __DIR__ . '/includes/single.php',
            'App\\SingleEventsController' => __DIR__ . '/includes/single_events.php',
            'App\\SingleRlEventsController' => __DIR__ . '/includes/single_rl_events.php',
        ];

        foreach ($mappings as $className => $template) {
            $this->assertSame($className, $provider->getControllerClassFromTemplate($template));
        }
    }

    /** @test */
    public function can_get_special_case_name_of_404_controller_from_template()
    {
        $app = new Application(__DIR__.'/../');
        $provider = new WordPressControllersServiceProvider($app);

        $this->assertSame('App\\Error404Controller', $provider->getControllerClassFromTemplate(__DIR__ . 'includes/404.php'));
    }

    /** @test */
    public function handle_template_include_applies_filters_on_controller_name_and_namespace()
    {
        $app = new Application(__DIR__.'/../');
        $provider = new WordPressControllersServiceProvider($app);

        Filters\expectApplied('lumberjack_controller_name')
            ->once()
            ->with('SingleController');

        Filters\expectApplied('lumberjack_controller_namespace')
            ->once()
            ->with('App\\');

        $provider->getControllerClassFromTemplate(__DIR__ . 'includes/single.php');
    }

    /** @test */
    public function handle_request_returns_false_if_controller_does_not_exist()
    {
        $app = new Application(__DIR__.'/../');
        $provider = new WordPressControllersServiceProvider($app);

        $response = $provider->handleRequest(new ServerRequest, 'Does\\Not\\Exist', 'handle');

        $this->assertFalse($response);
    }

    /** @test */
    public function handle_request_writes_warning_to_logs_if_controller_does_not_exist()
    {
        $log = Mockery::mock(Logger::class);
        $log->shouldReceive('warning')->once()->with('Controller class `Does\Not\Exist` not found');

        $app = new Application(__DIR__.'/../');
        $app->bind('logger', $log);
        $provider = new WordPressControllersServiceProvider($app);
        $provider->boot();

        $response = $provider->handleRequest(new ServerRequest, 'Does\\Not\\Exist', 'handle');
    }

    /** @test */
    public function handle_request_will_mark_request_handled_in_app_if_controller_does_exist()
    {
        $app = new Application(__DIR__.'/../');

        $provider = new WordPressControllersServiceProvider($app);
        $provider->boot();

        $response = $provider->handleRequest(new ServerRequest, TestController::class, 'handle');

        $this->assertTrue($app->hasRequestBeenHandled());
    }

    /** @test */
    public function handle_request_will_not_mark_request_handled_in_app_if_controller_does_not_exist()
    {
        $app = new Application(__DIR__.'/../');

        $provider = new WordPressControllersServiceProvider($app);
        $provider->boot();

        $response = $provider->handleRequest(new ServerRequest, 'Does\\Not\\Exist', 'handle');

        $this->assertFalse($app->hasRequestBeenHandled());
    }

    /** @test */
    public function handle_request_returns_response_when_controller_does_exist()
    {
        $app = new Application(__DIR__.'/../');

        $provider = new WordPressControllersServiceProvider($app);
        $provider->boot($app);

        $response = $provider->handleRequest(new ServerRequest, TestController::class, 'handle');

        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    /** @test */
    public function handle_request_returns_response_when_controller_returns_a_responsable()
    {
        $app = new Application(__DIR__.'/../');

        $provider = new WordPressControllersServiceProvider($app);
        $provider->boot($app);

        $response = $provider->handleRequest(new ServerRequest, TestControllerReturningAResponsable::class, 'handle');

        $this->assertInstanceOf(TextResponse::class, $response);
        $this->assertSame('testing123', $response->getBody()->getContents());
    }

    /** @test */
    public function handle_request_resolves_constructor_params_from_container()
    {
        $app = new Application(__DIR__.'/../');

        $provider = new WordPressControllersServiceProvider($app);
        $provider->boot($app);

        $response = $provider->handleRequest(new ServerRequest, TestControllerWithConstructorParams::class, 'handle');

        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    /** @test */
    public function handle_request_resolves_controller_method_params_from_container()
    {
        $app = new Application(__DIR__.'/../');

        $provider = new WordPressControllersServiceProvider($app);
        $provider->boot($app);

        $response = $provider->handleRequest(new ServerRequest, TestControllerWithHandleParams::class, 'handle');

        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    /** @test */
    public function handle_request_supports_middleware()
    {
        $app = new Application(__DIR__.'/../');
        $controller = new TestControllerWithMiddleware;
        $controller->middleware(new AddHeaderMiddleware('X-Header', 'testing123'));
        $app->bind(TestControllerWithMiddleware::class, $controller);

        $provider = new WordPressControllersServiceProvider($app);
        $provider->boot($app);

        $response = $provider->handleRequest(new ServerRequest, TestControllerWithMiddleware::class, 'handle');

        $this->assertTrue($response->hasHeader('X-Header'));
        $this->assertSame('testing123', $response->getHeader('X-Header')[0]);
    }

    /** @test */
    public function handle_request_supports_middleware_applied_to_a_specific_method_using_only()
    {
        $app = new Application(__DIR__.'/../');
        $controller = new TestControllerWithMiddleware;
        $controller->middleware(new AddHeaderMiddleware('X-Header', 'testing123'))->only('notHandle');
        $app->bind(TestControllerWithMiddleware::class, $controller);

        $provider = new WordPressControllersServiceProvider($app);
        $provider->boot($app);

        $response = $provider->handleRequest(new ServerRequest, TestControllerWithMiddleware::class, 'handle');

        $this->assertFalse($response->hasHeader('X-Header'));
    }

    /** @test */
    public function handle_request_supports_middleware_applied_to_a_specific_method_using_except()
    {
        $app = new Application(__DIR__.'/../');
        $controller = new TestControllerWithMiddleware;
        $controller->middleware(new AddHeaderMiddleware('X-Header', 'testing123'))->except('handle');
        $app->bind(TestControllerWithMiddleware::class, $controller);

        $provider = new WordPressControllersServiceProvider($app);
        $provider->boot($app);

        $response = $provider->handleRequest(new ServerRequest, TestControllerWithMiddleware::class, 'handle');

        $this->assertFalse($response->hasHeader('X-Header'));
    }

    /** @test */
    public function handle_request_supports_middleware_aliases()
    {
        Functions\when('get_bloginfo')->alias(function ($key) {
            if ($key === 'url') {
                return 'http://example.com';
            }
        });

        $app = new Application(__DIR__.'/../');

        $controller = new TestControllerWithMiddleware;
        $controller->middleware('middleware-key');
        $app->bind(TestControllerWithMiddleware::class, $controller);

        $routerProvider = new RouterServiceProvider($app);
        $provider = new WordPressControllersServiceProvider($app);
        $routerProvider->register();
        $routerProvider->boot();
        $provider->boot($app);

        $store = $app->get(MiddlewareAliases::class);
        $store->set('middleware-key', new AddHeaderMiddleware('X-Header', 'testing123'));

        $response = $provider->handleRequest(new ServerRequest, TestControllerWithMiddleware::class, 'handle');

        $this->assertTrue($response->hasHeader('X-Header'));
        $this->assertSame('testing123', $response->getHeader('X-Header')[0]);
    }

    /** @test */
    public function handle_template_include_will_call_app_shutdown_when_it_has_handled_a_request()
    {
        $response = new TextResponse('Testing 123', 404);
        $app = Mockery::mock(Application::class.'[shutdown]', [__DIR__.'/..']);
        $app->shouldReceive('shutdown')->times(1)->with($response);

        $provider = Mockery::mock(WordPressControllersServiceProvider::class.'[handleRequest]', [$app]);
        $provider->shouldReceive('handleRequest')->once()->andReturn($response);
        $provider->boot($app);

        $provider->handleTemplateInclude(__DIR__ . '/includes/single.php');
    }

    /** @test */
    public function handle_template_include_will_not_call_app_shutdown_when_it_has_not_handled_a_request()
    {
        $app = Mockery::mock(Application::class.'[shutdown]', [__DIR__.'/..']);
        $app->shouldReceive('shutdown')->times(0);

        $provider = Mockery::mock(WordPressControllersServiceProvider::class.'[handleRequest]', [$app]);
        $provider->shouldReceive('handleRequest')->once()->andReturn(false);
        $provider->boot($app);

        $provider->handleTemplateInclude(__DIR__ . '/includes/single.php');
    }
}

class TestController
{
    public function handle()
    {

    }
}

class TestControllerWithConstructorParams
{
    public function __construct(Application $app)
    {

    }

    public function handle()
    {

    }
}

class TestControllerWithHandleParams
{
    public function handle(Application $app)
    {

    }
}

class MyResponsable implements Responsable
{
    public function toResponse(RequestInterface $request) : ResponseInterface
    {
        return new TextResponse('testing123');
    }
}

class TestControllerReturningAResponsable
{
    public function handle()
    {
        return new MyResponsable;
    }
}

class TestControllerWithMiddleware extends Controller
{
    public function handle()
    {

    }
}

class AddHeaderMiddleware implements MiddlewareInterface
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
