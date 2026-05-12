<?php

namespace Rareloop\Lumberjack\Test\Bootstrappers;

use Brain\Monkey\Functions;
use Mockery;
use Rareloop\Lumberjack\Test\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Bootstrappers\RegisterExceptionHandler;
use Rareloop\Lumberjack\Config;
use Rareloop\Lumberjack\Http\ResponseEmitter;
use Rareloop\Lumberjack\Exceptions\Handler;
use Rareloop\Lumberjack\Exceptions\HandlerInterface;
use Rareloop\Lumberjack\Test\Unit\Concerns\BrainMonkeyPHPUnitIntegration;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use Rareloop\Router\Responsable;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\Response\TextResponse;
use Laminas\Diactoros\ServerRequest;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class RegisterExceptionHandlerTest extends TestCase
{
    use BrainMonkeyPHPUnitIntegration;

    #[Test]
    public function errors_are_converted_to_exceptions()
    {
        $this->expectException(\ErrorException::class);
        Functions\expect('is_admin')->once()->andReturn(false);

        $app = new Application();
        $config = new Config();
        $app->bind('config', $config);
        $app->bind(Config::class, $config);

        $bootstrapper = new RegisterExceptionHandler($app, new ResponseEmitter());
        $bootstrapper->bootstrap($app);
        $bootstrapper->handleError(E_USER_ERROR, 'Test Error');
    }

    #[Test]
    public function E_NOTICE_errors_are_not_converted_to_exceptions()
    {
        Functions\expect('is_admin')->once()->andReturn(false);

        $app = new Application();
        $handler = Mockery::mock(HandlerInterface::class);
        $app->bind(HandlerInterface::class, $handler);
        $config = new Config();
        $app->bind('config', $config);
        $app->bind(Config::class, $config);

        $handler->shouldReceive('report')->once()->with(Mockery::on(function ($e) {
            return $e->getSeverity() === E_NOTICE && $e->getMessage() === 'Test Error';
        }));

        $bootstrapper = new RegisterExceptionHandler($app, new ResponseEmitter());
        $bootstrapper->bootstrap($app);
        $bootstrapper->handleError(E_NOTICE, 'Test Error');
    }

    #[Test]
    public function E_WARNING_errors_are_not_converted_to_exceptions()
    {
        Functions\expect('is_admin')->once()->andReturn(false);

        $app = new Application();
        $handler = Mockery::mock(HandlerInterface::class);
        $app->bind(HandlerInterface::class, $handler);
        $config = new Config();
        $app->bind('config', $config);
        $app->bind(Config::class, $config);

        $handler->shouldReceive('report')->once()->with(Mockery::on(function ($e) {
            return $e->getSeverity() === E_WARNING && $e->getMessage() === 'Test Error';
        }));

        $bootstrapper = new RegisterExceptionHandler($app, new ResponseEmitter());
        $bootstrapper->bootstrap($app);
        $bootstrapper->handleError(E_WARNING, 'Test Error');
    }

    #[Test]
    public function E_USER_NOTICE_errors_are_not_converted_to_exceptions()
    {
        Functions\expect('is_admin')->once()->andReturn(false);

        $app = new Application();
        $handler = Mockery::mock(HandlerInterface::class);
        $app->bind(HandlerInterface::class, $handler);
        $config = new Config();
        $app->bind('config', $config);
        $app->bind(Config::class, $config);

        $handler->shouldReceive('report')->once()->with(Mockery::on(function ($e) {
            return $e->getSeverity() === E_USER_NOTICE && $e->getMessage() === 'Test Error';
        }));

        $bootstrapper = new RegisterExceptionHandler($app, new ResponseEmitter());
        $bootstrapper->bootstrap($app);
        $bootstrapper->handleError(E_USER_NOTICE, 'Test Error');
    }

    #[Test]
    public function E_USER_DEPRECATED_errors_are_not_converted_to_exceptions()
    {
        Functions\expect('is_admin')->once()->andReturn(false);

        $app = new Application();
        $handler = Mockery::mock(HandlerInterface::class);
        $app->bind(HandlerInterface::class, $handler);
        $config = new Config();
        $app->bind('config', $config);
        $app->bind(Config::class, $config);

        $handler->shouldReceive('report')->once()->with(Mockery::on(function ($e) {
            return $e->getSeverity() === E_USER_DEPRECATED && $e->getMessage() === 'Test Error';
        }));

        $bootstrapper = new RegisterExceptionHandler($app, new ResponseEmitter());
        $bootstrapper->bootstrap($app);
        $bootstrapper->handleError(E_USER_DEPRECATED, 'Test Error');
    }

    #[Test]
    public function E_DEPRECATED_errors_are_not_converted_to_exceptions()
    {
        Functions\expect('is_admin')->once()->andReturn(false);

        $app = new Application();
        $handler = Mockery::mock(HandlerInterface::class);
        $app->bind(HandlerInterface::class, $handler);
        $config = new Config();
        $app->bind('config', $config);
        $app->bind(Config::class, $config);

        $handler->shouldReceive('report')->once()->with(Mockery::on(function ($e) {
            return $e->getSeverity() === E_DEPRECATED && $e->getMessage() === 'Test Error';
        }));

        $bootstrapper = new RegisterExceptionHandler($app, new ResponseEmitter());
        $bootstrapper->bootstrap($app);
        $bootstrapper->handleError(E_DEPRECATED, 'Test Error');
    }

    #[Test]
    public function custom_error_level_can_be_set_for_report_only()
    {
        $this->expectException(\ErrorException::class);
        Functions\expect('is_admin')->once()->andReturn(false);

        $app = new Application();
        $handler = Mockery::mock(HandlerInterface::class);
        $app->bind(HandlerInterface::class, $handler);
        $config = new Config();
        $config->set('app.errors.reportOnly', [E_USER_ERROR]);
        $app->bind('config', $config);
        $app->bind(Config::class, $config);

        $handler->shouldReceive('report')->once()->with(Mockery::on(function ($e) {
            return $e->getSeverity() === E_USER_ERROR && $e->getMessage() === 'Test Error';
        }));

        $bootstrapper = new RegisterExceptionHandler($app, new ResponseEmitter());
        $bootstrapper->bootstrap($app);
        $bootstrapper->handleError(E_USER_ERROR, 'Test Error');
        $bootstrapper->handleError(E_USER_DEPRECATED, 'Test Error');
    }

    #[Test]
    public function handle_exception_should_call_handlers_report_and_render_methods()
    {
        Functions\expect('is_admin')->once()->andReturn(false);

        $app = new Application();

        $exception = new \Exception('Test Exception');
        $request = new ServerRequest([], [], '/test/123', 'GET');
        $app->bind('request', $request);

        $handler = Mockery::mock(Handler::class);
        $handler->shouldReceive('report')->with($exception)->once();
        $handler->shouldReceive('render')->with($request, $exception)->once()->andReturn(new Response());
        $app->bind(HandlerInterface::class, $handler);

        $bootstrapper = Mockery::mock(RegisterExceptionHandler::class . '[send]', [$app, new ResponseEmitter()]);
        $bootstrapper->shouldReceive('send')->once();
        $bootstrapper->bootstrap($app);

        $bootstrapper->handleException($exception);
    }

    #[Test]
    public function handle_exception_should_call_handlers_report_and_render_methods_using_an_error()
    {
        Functions\expect('is_admin')->once()->andReturn(false);

        $app = new Application();

        $error = new \Error('Test Exception');
        $request = new ServerRequest([], [], '/test/123', 'GET');
        $app->bind('request', $request);

        $handler = Mockery::mock(Handler::class);
        $handler->shouldReceive('report')->with(Mockery::type(\ErrorException::class))->once();
        $handler->shouldReceive('render')
            ->with($request, Mockery::type(\ErrorException::class))
            ->once()
            ->andReturn(new Response());
        $app->bind(HandlerInterface::class, $handler);

        $bootstrapper = Mockery::mock(RegisterExceptionHandler::class . '[send]', [$app, new ResponseEmitter()]);
        $bootstrapper->shouldReceive('send')->once();
        $bootstrapper->bootstrap($app);

        $bootstrapper->handleException($error);
    }

    #[Test]
    public function handle_exception_calls_handler_report_and_render_even_if_request_is_not_set()
    {
        Functions\expect('is_admin')->once()->andReturn(false);

        $app = new Application();

        $exception = new \Exception('Test Exception');

        $handler = Mockery::mock(Handler::class);
        $handler->shouldReceive('report')->with($exception)->once();
        $handler->shouldReceive('render')
            ->with(Mockery::type(ServerRequest::class), $exception)
            ->once()
            ->andReturn(new Response());
        $app->bind(HandlerInterface::class, $handler);

        $bootstrapper = Mockery::mock(RegisterExceptionHandler::class . '[send]', [$app, new ResponseEmitter()]);
        $bootstrapper->shouldReceive('send')->once();
        $bootstrapper->bootstrap($app);

        $bootstrapper->handleException($exception);
    }

    #[Test]
    public function handle_exception_should_not_call_render_methods_when_exception_is_responsable()
    {
        Functions\expect('is_admin')->once()->andReturn(false);

        $app = new Application();

        $request = new ServerRequest([], [], '/test/123', 'GET');
        $app->bind('request', $request);

        $exception = Mockery::mock(ResponsableException::class);
        $exception->shouldReceive('toResponse')->with($request)->once();

        $handler = Mockery::mock(Handler::class);
        $handler->shouldReceive('report');
        $handler->shouldNotReceive('render');
        $app->bind(HandlerInterface::class, $handler);

        $bootstrapper = Mockery::mock(RegisterExceptionHandler::class . '[send]', [$app, new ResponseEmitter()]);
        $bootstrapper->shouldReceive('send')->once();
        $bootstrapper->bootstrap($app);

        $bootstrapper->handleException($exception);
    }

    #[Test]
    public function send_should_use_the_emitter()
    {
        $app = new Application();
        $response = new TextResponse('Hello World');
        $emitter = Mockery::mock(ResponseEmitter::class);
        $emitter->shouldReceive('emit')->once()->with($response);

        $bootstrapper = new RegisterExceptionHandler($app, $emitter);

        $bootstrapper->send($response);
    }
}

class ResponsableException extends \Exception implements Responsable
{
    public function toResponse(RequestInterface $request): ResponseInterface
    {
        return new TextResponse('testing123');
    }
}
