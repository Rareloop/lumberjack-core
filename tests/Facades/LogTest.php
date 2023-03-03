<?php

namespace Rareloop\Lumberjack\Test\Facades;

use Blast\Facades\FacadeFactory;
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
        FacadeFactory::setContainer($app);

        $logger = new Logger('app');
        $app->bind('logger', $logger);

        $this->assertInstanceOf(Logger::class, LogFacade::__instance());
        $this->assertSame($logger, LogFacade::__instance());
    }
}
