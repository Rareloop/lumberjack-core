<?php

namespace Rareloop\Lumberjack\Test\Providers;

use Brain\Monkey\Functions;
use PHPUnit\Framework\Attributes\Test;
use Rareloop\Lumberjack\Test\TestCase;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Config;
use Rareloop\Lumberjack\Providers\ServiceProvider;
use Rareloop\Lumberjack\Test\Unit\Concerns\BrainMonkeyPHPUnitIntegration;

class ServiceProviderTest extends TestCase
{
    use BrainMonkeyPHPUnitIntegration;

    #[Test]
    public function can_merge_config_from_a_file(): void
    {
        $config = new Config();
        $app = new Application();
        $app->bind(Config::class, $config);
        $provider = new TestServiceProvider($app);

        $provider->mergeConfigFrom(__DIR__ . '/../config/another.php', 'another');

        $this->assertSame(123, $config->get('another.test'));
    }

    #[Test]
    public function existing_config_takes_priority_over_merged_values(): void
    {
        $config = new Config();
        $app = new Application();
        $app->bind(Config::class, $config);
        $provider = new TestServiceProvider($app);

        $config->set('another.test', 456);
        $provider->mergeConfigFrom(__DIR__ . '/../config/another.php', 'another');

        $this->assertSame(456, $config->get('another.test'));
    }
}

class TestServiceProvider extends ServiceProvider
{
}
