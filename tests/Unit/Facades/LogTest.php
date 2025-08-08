<?php

namespace Rareloop\Lumberjack\Test\Facades;

use Illuminate\Support\Facades\Facade;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Facades\Log as LogFacade;

class LogTest extends TestCase
{
    /** @test */
    public function test_facade()
    {
        $app = new Application();
        Facade::setFacadeApplication($app);

        $logger = new Logger('app');
        $app->bind('logger', $logger);

        $this->assertInstanceOf(Logger::class, LogFacade::getFacadeRoot());
        $this->assertSame($logger, LogFacade::getFacadeRoot());
    }
}
