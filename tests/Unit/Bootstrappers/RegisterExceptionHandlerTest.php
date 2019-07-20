<?php

namespace Rareloop\Lumberjack\Test\Bootstrappers;

use Brain\Monkey\Functions;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Bootstrappers\RegisterExceptionHandler;
use Rareloop\Lumberjack\Config;
use Rareloop\Lumberjack\Exceptions\Handler;
use Rareloop\Lumberjack\Exceptions\HandlerInterface;
use Rareloop\Lumberjack\Test\Unit\BrainMonkeyPHPUnitIntegration;
use Rareloop\Router\Responsable;
use Symfony\Component\Debug\Exception\FatalErrorException;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\TextResponse;
use Zend\Diactoros\ServerRequest;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class RegisterExceptionHandlerTest extends TestCase
{
    use BrainMonkeyPHPUnitIntegration;

    /**
     * @test
     * @expectedException     ErrorException
     */
    public function errors_are_converted_to_exceptions()
    {
        Functions\expect('is_admin')->once()->andReturn(false);

        $app = new Application;

        $bootstrapper = new RegisterExceptionHandler();
        $bootstrapper->bootstrap($app);
        $bootstrapper->handleError(E_USER_ERROR, 'Test Error');
    }

    /**
     * @test
     */
    public function E_USER_NOTICE_errors_are_not_converted_to_exceptions()
    {
        Functions\expect('is_admin')->once()->andReturn(false);

        $app = new Application;
        $handler = Mockery::mock(HandlerInterface::class);
        $app->bind(HandlerInterface::class, $handler);

        $handler->shouldReceive('report')->once()->with(Mockery::on(function ($e) {
            return $e->getSeverity() === E_USER_NOTICE && $e->getMessage() === 'Test Error';
        }));

        $bootstrapper = new RegisterExceptionHandler();
        $bootstrapper->bootstrap($app);
        $bootstrapper->handleError(E_USER_NOTICE, 'Test Error');
    }

    /**
     * @test
     */
    public function E_USER_DEPRECATED_errors_are_not_converted_to_exceptions()
    {
        Functions\expect('is_admin')->once()->andReturn(false);

        $app = new Application;
        $handler = Mockery::mock(HandlerInterface::class);
        $app->bind(HandlerInterface::class, $handler);

        $handler->shouldReceive('report')->once()->with(Mockery::on(function ($e) {
            return $e->getSeverity() === E_USER_DEPRECATED && $e->getMessage() === 'Test Error';
        }));

        $bootstrapper = new RegisterExceptionHandler();
        $bootstrapper->bootstrap($app);
        $bootstrapper->handleError(E_USER_DEPRECATED, 'Test Error');
    }

    /** @test */
    public function handle_exception_should_call_handlers_report_and_render_methods()
    {
        Functions\expect('is_admin')->once()->andReturn(false);

        $app = new Application;

        $exception = new \Exception('Test Exception');
        $request = new ServerRequest([], [], '/test/123', 'GET');
        $app->bind('request', $request);

        $handler = Mockery::mock(Handler::class);
        $handler->shouldReceive('report')->with($exception)->once();
        $handler->shouldReceive('render')->with($request, $exception)->once()->andReturn(new Response());
        $app->bind(HandlerInterface::class, $handler);

        $bootstrapper = Mockery::mock(RegisterExceptionHandler::class.'[send]');
        $bootstrapper->shouldReceive('send')->once();
        $bootstrapper->bootstrap($app);

        $bootstrapper->handleException($exception);
    }

    /** @test */
    public function handle_exception_should_call_handlers_report_and_render_methods_using_an_error()
    {
        Functions\expect('is_admin')->once()->andReturn(false);

        $app = new Application;

        $error = new \Error('Test Exception');
        $request = new ServerRequest([], [], '/test/123', 'GET');
        $app->bind('request', $request);

        $handler = Mockery::mock(Handler::class);
        $handler->shouldReceive('report')->with(Mockery::type(\ErrorException::class))->once();
        $handler->shouldReceive('render')->with($request, Mockery::type(\ErrorException::class))->once()->andReturn(new Response());
        $app->bind(HandlerInterface::class, $handler);

        $bootstrapper = Mockery::mock(RegisterExceptionHandler::class.'[send]');
        $bootstrapper->shouldReceive('send')->once();
        $bootstrapper->bootstrap($app);

        $bootstrapper->handleException($error);
    }

    /** @test */
    public function handle_exception_should_call_handlers_report_and_render_methods_even_if_request_is_not_set_in_the_container()
    {
        Functions\expect('is_admin')->once()->andReturn(false);

        $app = new Application;

        $exception = new \Exception('Test Exception');

        $handler = Mockery::mock(Handler::class);
        $handler->shouldReceive('report')->with($exception)->once();
        $handler->shouldReceive('render')->with(Mockery::type(ServerRequest::class), $exception)->once()->andReturn(new Response());
        $app->bind(HandlerInterface::class, $handler);

        $bootstrapper = Mockery::mock(RegisterExceptionHandler::class.'[send]');
        $bootstrapper->shouldReceive('send')->once();
        $bootstrapper->bootstrap($app);

        $bootstrapper->handleException($exception);
    }

    /** @test */
    public function handle_exception_should_not_call_render_methods_when_exception_is_responsable()
    {
        Functions\expect('is_admin')->once()->andReturn(false);

        $app = new Application;

        $request = new ServerRequest([], [], '/test/123', 'GET');
        $app->bind('request', $request);

        $exception = Mockery::mock(ResponsableException::class);
        $exception->shouldReceive('toResponse')->with($request)->once();

        $handler = Mockery::mock(Handler::class);
        $handler->shouldReceive('report');
        $handler->shouldNotReceive('render');
        $app->bind(HandlerInterface::class, $handler);

        $bootstrapper = Mockery::mock(RegisterExceptionHandler::class.'[send]');
        $bootstrapper->shouldReceive('send')->once();
        $bootstrapper->bootstrap($app);

        $bootstrapper->handleException($exception);
    }
}

class ResponsableException extends \Exception implements Responsable
{
    public function toResponse(RequestInterface $request) : ResponseInterface
    {
        return new TextResponse('testing123');
    }
}
