<?php

namespace Rareloop\Lumberjack\Test;

use Mockery;
use PHPUnit\Framework\TestCase;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Config;
use Rareloop\Lumberjack\Session\FileSessionHandler;
use Rareloop\Lumberjack\Session\SessionManager;
use Rareloop\Lumberjack\Session\Store;
use Rareloop\Lumberjack\Test\Unit\Session\NullSessionHandler;

class SessionManagerTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private function appWithSessionDriverConfig($driver, $cookie = 'lumberjack')
    {
        $app = new Application;
        $config = Mockery::mock(Config::class.'[get]');
        $config->shouldReceive('get')->with('session.cookie', 'lumberjack')->andReturn($cookie);
        $config->shouldReceive('get')->with('session.driver', 'file')->andReturn($driver);
        $app->bind(Config::class, $config);

        return $app;
    }

    /** @test */
    public function default_driver_is_read_from_config()
    {
        $app = $this->appWithSessionDriverConfig('driver-name');

        $manager = new SessionManager($app);

        $this->assertSame('driver-name', $manager->getDefaultDriver());
    }

    /** @test */
    public function can_create_a_file_driver()
    {
        $app = $this->appWithSessionDriverConfig('file', 'lumberjack');

        $manager = new SessionManager($app);

        $this->assertInstanceOf(FileSessionHandler::class, $manager->driver()->getHandler());
        $this->assertSame('lumberjack', $manager->driver()->getName());
    }

    /** @test */
    public function can_extend_list_of_drivers()
    {
        $app = new Application;
        $manager = new SessionManager($app);

        $manager->extend('test', function () {
            return new Store('name', new TestSessionHandler);
        });

        $this->assertInstanceOf(TestSessionHandler::class, $manager->driver('test')->getHandler());
    }
}

class TestSessionHandler extends NullSessionHandler {}
