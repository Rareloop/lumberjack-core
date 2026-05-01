<?php

namespace Rareloop\Lumberjack\Test\Providers;

use Brain\Monkey\Functions;
use Mockery;
use PHPUnit\Framework\TestCase;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Config;
use Rareloop\Lumberjack\Providers\IgnitionServiceProvider;
use Spatie\Ignition\Ignition;
use Rareloop\Lumberjack\Test\Unit\BrainMonkeyPHPUnitIntegration;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class IgnitionServiceProviderTest extends TestCase
{
    use BrainMonkeyPHPUnitIntegration;

    /** @test */
    public function ignition_is_bound_as_a_singleton_in_the_container()
    {
        $app = new Application;
        $config = new Config();
        $app->bind('config', $config);

        $provider = new IgnitionServiceProvider($app);
        $provider->register();

        $this->assertTrue($app->has(Ignition::class));
        $this->assertInstanceOf(Ignition::class, $app->get(Ignition::class));
    }
}
