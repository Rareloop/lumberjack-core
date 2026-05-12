<?php

namespace Rareloop\Lumberjack\Test\Providers;

use Rareloop\Lumberjack\Test\TestCase;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Config;
use Rareloop\Lumberjack\Providers\IgnitionServiceProvider;
use Spatie\Ignition\Ignition;
use Rareloop\Lumberjack\Test\Unit\Concerns\BrainMonkeyPHPUnitIntegration;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\Attributes\PreserveGlobalState;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class IgnitionServiceProviderTest extends TestCase
{
    use BrainMonkeyPHPUnitIntegration;

    #[Test]
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
