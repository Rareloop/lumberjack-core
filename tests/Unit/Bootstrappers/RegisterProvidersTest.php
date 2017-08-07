<?php

namespace Rareloop\Lumberjack\Test\Bootstrappers;

use Mockery;
use PHPUnit\Framework\TestCase;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Bootstrappers\LoadConfiguration;
use Rareloop\Lumberjack\Bootstrappers\RegisterProviders;
use Rareloop\Lumberjack\Config;

class RegisterProvidersTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    /** @test */
    public function registers_all_providers_found_in_config()
    {
        $app = new Application;

        $provider1 = Mockery::mock(new RPTestServiceProvider1);
        $provider1->shouldReceive('register')->with($app)->once();
        $provider2 = Mockery::mock(new RPTestServiceProvider2);
        $provider2->shouldReceive('register')->with($app)->once();

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

class RPTestServiceProvider1
{
    public function register() {}
}

class RPTestServiceProvider2
{
    public function register() {}
}
