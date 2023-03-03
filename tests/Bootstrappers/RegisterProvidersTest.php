<?php

namespace Rareloop\Lumberjack\Test\Bootstrappers;

use Mockery;
use PHPUnit\Framework\TestCase;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Bootstrappers\LoadConfiguration;
use Rareloop\Lumberjack\Bootstrappers\RegisterProviders;
use Rareloop\Lumberjack\Config;
use Rareloop\Lumberjack\Providers\ServiceProvider;

class RegisterProvidersTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    /** @test */
    public function registers_all_providers_found_in_config()
    {
        $app = new Application;

        $provider1 = Mockery::mock(RPTestServiceProvider1::class, [$app]);
        $provider1->shouldReceive('register')->once();
        $provider2 = Mockery::mock(RPTestServiceProvider2::class, [$app]);
        $provider2->shouldReceive('register')->once();

        $config = new Config;
        $config->set('app.providers', [
            $provider1,
            $provider2,
        ]);
        $app->bind('config', $config);

        $registerProvidersBootstrapper = new RegisterProviders;
        $registerProvidersBootstrapper->bootstrap($app);
    }

    /** @test */
    public function should_not_fall_over_on_empty_config_data()
    {
        $app = new Application;

        $config = new Config;
        $app->bind('config', $config);

        $registerProvidersBootstrapper = new RegisterProviders;
        $registerProvidersBootstrapper->bootstrap($app);

        $this->addToAssertionCount(1);  // does not throw an exception
    }
}

class RPTestServiceProvider1 extends ServiceProvider
{
    public function register() {}
}

class RPTestServiceProvider2 extends ServiceProvider
{
    public function register() {}
}
