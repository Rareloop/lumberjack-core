<?php

namespace Rareloop\Lumberjack\Test\Bootstrappers;

use Mockery;
use PHPUnit\Framework\TestCase;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Bootstrappers\LoadConfiguration;
use Rareloop\Lumberjack\Bootstrappers\RegisterProviders;
use Rareloop\Lumberjack\Config;
use Rareloop\Lumberjack\Autodiscovery\AutodiscoveredPackages;
use Rareloop\Lumberjack\Providers\ServiceProvider;

class RegisterProvidersTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    /** @test */
    public function registers_all_providers_found_in_config()
    {
        $app = new Application;

        $manifest = Mockery::mock(AutodiscoveredPackages::class);
        $manifest->shouldReceive('providers')->andReturn([]);
        $app->bind(AutodiscoveredPackages::class, $manifest);

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
    public function user_provided_instance_takes_precedence_over_autodiscovered_class_string()
    {
        $app = new Application;

        // The user's specific instance they want to use
        $userInstance = new RPTestServiceProvider1($app);
        $userInstance->foo = 'bar';

        // Autodiscovery finds the class name
        $manifest = Mockery::mock(AutodiscoveredPackages::class);
        $manifest->shouldReceive('providers')->andReturn([
            RPTestServiceProvider1::class,
        ]);
        $app->bind(AutodiscoveredPackages::class, $manifest);

        // User configures the specific instance
        $config = new Config;
        $config->set('app.providers', [
            $userInstance,
        ]);
        $app->bind('config', $config);

        $registerProvidersBootstrapper = new RegisterProviders;
        $registerProvidersBootstrapper->bootstrap($app);

        // Verify that the instance registered in the app is the one the user provided
        $registeredProvider = $app->getProvider(RPTestServiceProvider1::class);
        $this->assertSame($userInstance, $registeredProvider);
        $this->assertEquals('bar', $registeredProvider->foo);
    }
}

class RPTestServiceProvider1 extends ServiceProvider
{
    public $foo;

    public function register() {}
}

class RPTestServiceProvider2 extends ServiceProvider
{
    public function register() {}
}
