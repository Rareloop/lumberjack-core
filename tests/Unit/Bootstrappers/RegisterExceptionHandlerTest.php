<?php

namespace Rareloop\Lumberjack\Test\Bootstrappers;

use Mockery;
use PHPUnit\Framework\TestCase;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Bootstrappers\RegisterExceptionHandler;
use Rareloop\Lumberjack\Config;
use Rareloop\Lumberjack\Exceptions\Handler;
use Rareloop\Lumberjack\Exceptions\HandlerInterface;
use Symfony\Component\Debug\Exception\FatalErrorException;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;

class RegisterExceptionHandlerTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    /**
     * @test
     * @expectedException     ErrorException
     */
    public function errors_are_converted_to_exceptions()
    {
        $app = new Application;

        $bootstrapper = new RegisterExceptionHandler();
        $bootstrapper->bootstrap($app);
        $bootstrapper->handleError(E_USER_ERROR, 'Test Error');
    }

    /** @test */
    public function handle_exception_should_call_handlers_report_and_render_methods()
    {
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
}
