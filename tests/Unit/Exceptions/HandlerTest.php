<?php

namespace Rareloop\Lumberjack\Test\Exceptions;

use Mockery;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Rareloop\Lumberjack\Config;
use Zend\Diactoros\ServerRequest;
use Rareloop\Lumberjack\Application;
use Illuminate\Support\Facades\Facade;
use Zend\Diactoros\Response\HtmlResponse;
use Rareloop\Lumberjack\Exceptions\Handler;
use Rareloop\Lumberjack\Facades\Config as ConfigFacade;

class HandlerTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    /** @test */
    public function report_should_log_exception()
    {
        $app = new Application;

        $exception = new \Exception('Test Exception');

        $logger = Mockery::mock(Logger::class);
        $logger->shouldReceive('error')->with($exception)->once();
        $app->bind('logger', $logger);

        $handler = new Handler($app);

        $handler->report($exception);
    }

    /** @test */
    public function blacklisted_exception_types_will_not_be_logged()
    {
        $app = new Application;

        $exception = new BlacklistedException('Test Exception');

        $logger = Mockery::mock(Logger::class);
        $logger->shouldNotReceive('error');
        $app->bind('logger', $logger);

        $handler = new HandlerWithBlacklist($app);

        $handler->report($exception);
    }

    /** @test */
    public function render_should_return_an_html_response_when_debug_is_enabled()
    {
        $app = new Application;
        Facade::setFacadeApplication($app);
        $config = new Config;
        $config->set('app.debug', true);
        $app->bind('config', $config);

        $exception = new \Exception('Test Exception');
        $handler = new Handler($app);

        $response = $handler->render(new ServerRequest, $exception);

        $this->assertInstanceOf(HtmlResponse::class, $response);
    }

    /** @test */
    public function render_should_return_an_html_response_when_debug_is_disabled()
    {
        $app = new Application;
        Facade::setFacadeApplication($app);
        $config = new Config;
        $config->set('app.debug', false);
        $app->bind('config', $config);

        $exception = new \Exception('Test Exception');
        $handler = new Handler($app);

        $response = $handler->render(new ServerRequest, $exception);

        $this->assertInstanceOf(HtmlResponse::class, $response);
    }

    /** @test */
    public function render_should_include_stack_trace_when_debug_is_enabled()
    {
        $app = new Application;
        Facade::setFacadeApplication($app);
        $config = new Config;
        $config->set('app.debug', true);
        $app->bind('config', $config);

        $exception = new \Exception('Test Exception');
        $handler = new Handler($app);

        $response = $handler->render(new ServerRequest, $exception);

        $this->assertStringContainsString('Test Exception', $response->getBody()->getContents());
    }

    /** @test */
    public function render_should_not_include_stack_trace_when_debug_is_disabled()
    {
        $app = new Application;
        Facade::setFacadeApplication($app);
        Facade::clearResolvedInstances();
        $config = new Config;
        $config->set('app.debug', false);
        $app->bind('config', $config);

        $exception = new \Exception('Test Exception');
        $handler = new Handler($app);

        $response = $handler->render(new ServerRequest, $exception);

        $this->assertStringNotContainsString('Test Exception', $response->getBody()->getContents());
    }
}

class HandlerWithBlacklist extends Handler
{
    protected $dontReport = [
        BlacklistedException::class,
    ];
}

class BlacklistedException extends \Exception {}
