<?php

namespace Rareloop\Lumberjack\Test\Facades;

use Blast\Facades\FacadeFactory;
use PHPUnit\Framework\TestCase;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Facades\Session;
use Rareloop\Lumberjack\Session\SessionManager;
use Rareloop\Lumberjack\Test\Unit\Session\NullSessionHandler;

class SessionTest extends TestCase
{
    /** @test */
    public function test_facade()
    {
        $app = new Application();
        FacadeFactory::setContainer($app);

        $store = new SessionManager($app);
        $app->bind('session', $store);

        $this->assertInstanceOf(SessionManager::class, Session::__instance());
    }
}
