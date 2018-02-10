<?php

namespace Rareloop\Lumberjack\Test\Exceptions;

use Blast\Facades\FacadeFactory;
use Mockery;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Config;
use Rareloop\Lumberjack\Exceptions\Handler;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\ServerRequest;

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
        FacadeFactory::setContainer($app);
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
        FacadeFactory::setContainer($app);
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
        FacadeFactory::setContainer($app);
        $config = new Config;
        $config->set('app.debug', true);
        $app->bind('config', $config);

        $exception = new \Exception('Test Exception');
        $handler = new Handler($app);

        $response = $handler->render(new ServerRequest, $exception);

        $this->assertContains('Test Exception', $response->getBody()->getContents());
    }

    /** @test */
    public function render_should_not_include_stack_trace_when_debug_is_disabled()
    {
        $app = new Application;
        FacadeFactory::setContainer($app);
        $config = new Config;
        $config->set('app.debug', false);
        $app->bind('config', $config);

        $exception = new \Exception('Test Exception');
        $handler = new Handler($app);

        $response = $handler->render(new ServerRequest, $exception);

        $this->assertNotContains('Test Exception', $response->getBody()->getContents());
    }
}

class HandlerWithBlacklist extends Handler
{
    protected $dontReport = [
        BlacklistedException::class,
    ];
}

class BlacklistedException extends \Exception {}
