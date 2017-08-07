<?php

namespace Rareloop\Lumberjack\Test\Bootstrappers;

use Mockery;
use PHPUnit\Framework\TestCase;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Bootstrappers\LoadConfiguration;
use Rareloop\Lumberjack\Bootstrappers\BootProviders;
use Rareloop\Lumberjack\Config;

class BootProvidersTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    /** @test */
    public function boots_all_registered_providers()
    {
        $app = new Application;

        $provider1 = Mockery::mock(new TestServiceProvider1);
        $provider1->shouldReceive('boot')->with($app)->once();
        $provider2 = Mockery::mock(new TestServiceProvider2);
        $provider2->shouldReceive('boot')->with($app)->once();

        $app->register($provider1);
        $app->register($provider2);

        $bootProvidersBootstrapper = new BootProviders;
        $bootProvidersBootstrapper->bootstrap($app);
    }
}

class TestServiceProvider1
{
    public function boot(Application $app) {}
}

class TestServiceProvider2
{
    public function boot(Application $app) {}
}
