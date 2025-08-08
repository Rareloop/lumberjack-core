<?php

namespace Rareloop\Lumberjack\Test\Facades;

use Illuminate\Support\Facades\Facade;
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
        Facade::setFacadeApplication($app);

        $store = new SessionManager($app);
        $app->bind('session', $store);

        $this->assertInstanceOf(SessionManager::class, Session::getFacadeRoot());
    }
}
