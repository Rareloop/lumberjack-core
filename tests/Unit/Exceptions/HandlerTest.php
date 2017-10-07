<?php

namespace Rareloop\Lumberjack\Test\Exceptions;

use Mockery;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Exceptions\Handler;

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
}

class HandlerWithBlacklist extends Handler
{
    protected $dontReport = [
        BlacklistedException::class,
    ];
}

class BlacklistedException extends \Exception {}
