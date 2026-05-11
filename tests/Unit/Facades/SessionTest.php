<?php

namespace Rareloop\Lumberjack\Test\Facades;

use PHPUnit\Framework\Attributes\Test;
use Rareloop\Lumberjack\Test\TestCase;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\FacadeFactory;
use Rareloop\Lumberjack\Facades\Session;
use Rareloop\Lumberjack\Session\SessionManager;
use Rareloop\Lumberjack\Test\Unit\Session\NullSessionHandler;

class SessionTest extends TestCase
{
    #[Test]
    public function test_facade()
    {
        $app = new Application();
        FacadeFactory::setContainer($app);

        $store = new SessionManager($app);
        $app->bind('session', $store);

        $this->assertInstanceOf(SessionManager::class, Session::__instance());
    }
}
