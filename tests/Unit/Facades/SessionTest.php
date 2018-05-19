<?php

namespace Rareloop\Lumberjack\Test\Facades;

use Blast\Facades\FacadeFactory;
use PHPUnit\Framework\TestCase;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Facades\Session;
use Rareloop\Lumberjack\Session\Store;
use Rareloop\Lumberjack\Test\Unit\Session\NullSessionHandler;

class SessionTest extends TestCase
{
    /** @test */
    public function test_facade()
    {
        $app = new Application();
        FacadeFactory::setContainer($app);

        $store = new Store('session-name', new NullSessionHandler, 'session-id');
        $app->bind('session', $store);

        $this->assertInstanceOf(Store::class, Session::__instance());
    }
}
