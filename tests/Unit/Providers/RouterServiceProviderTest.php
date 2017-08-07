<?php

namespace Rareloop\Lumberjack\Test\Providers;

use Brain\Monkey;
use PHPUnit\Framework\TestCase;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Http\Kernal;
use Rareloop\Lumberjack\Providers\RouterServiceProvider;
use Rareloop\Router\Router;

class RouterServiceProviderTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    public function setUp()
    {
        parent::setUp();
        Monkey\setUp();
    }

    public function tearDown()
    {
        parent::tearDown();
        Monkey\tearDown();
    }

    /** @test */
    public function router_object_is_configured()
    {
        $app = new Application(__DIR__.'/../');
        $kernal = new TestKernal($app);

        $app->register(new RouterServiceProvider);
        $kernal->bootstrap();

        $this->assertTrue($app->has('router'));
        $this->assertSame($app->get('router'), $app->get(Router::class));
    }
}

class TestKernal extends Kernal {}
