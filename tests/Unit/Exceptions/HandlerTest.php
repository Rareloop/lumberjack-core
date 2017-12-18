<?php

namespace Rareloop\Lumberjack\Test\Exceptions;

use Blast\Facades\FacadeFactory;
use Mockery;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Config;
use Rareloop\Lumberjack\Bootstrappers\LoadConfiguration;
use Rareloop\Lumberjack\Exceptions\Handler;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\ServerRequest;
use Rareloop\Lumberjack\Providers\LogServiceProvider;

class HandlerTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;


    protected function setUp()
    {
        parent::setUp();
        @unlink(realpath(__DIR__.'/../../../').'/app.log');
        $this->app = new Application(__DIR__ . '/../');
        $bootstrapper = new LoadConfiguration;

        $bootstrapper->bootstrap($this->app);
    }

    protected function tearDown()
    {
        parent::tearDown();
        @unlink(realpath(__DIR__.'/../../../').'/app.log');
    }

    /** @test */
    public function report_should_log_exception_when_logging_is_turned_on()
    {
        $config = $this->app->get('config');
        $config->set('app.logs.enabled', true);
        $exception = new \Exception('Test Exception');

        $this->app->register(LogServiceProvider::class);

        $handler = new Handler($this->app);

        $handler->report($exception);

        $logPath = realpath(__DIR__.'/../../../').'/app.log';

        $this->assertTrue(file_exists($logPath));
    }

    /**
     * @test
     * @codingStandardsIgnoreLine */
    function logging_does_not_write_to_file_by_default()
    {
        $exception = new \Exception('Test Exception');

        $this->app->register(LogServiceProvider::class);

        $handler = new Handler($this->app);

        $handler->report($exception);
        $logPath = realpath(__DIR__.'/../../../').'/app.log';

        $this->assertFalse(file_exists($logPath));
    }

    /** @test */
    public function blacklisted_exception_types_will_not_be_logged()
    {
        $exception = new BlacklistedException('Test Exception');

        $logger = Mockery::mock(Logger::class);
        $logger->shouldNotReceive('error');
        $this->app->bind('logger', $logger);

        $handler = new HandlerWithBlacklist($this->app);

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
