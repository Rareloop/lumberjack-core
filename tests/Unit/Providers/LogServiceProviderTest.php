<?php

namespace Rareloop\Lumberjack\Test\Providers;

use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Bootstrappers\RegisterProviders;
use Rareloop\Lumberjack\Config;
use Rareloop\Lumberjack\Http\Lumberjack;
use Rareloop\Lumberjack\Test\Unit\BrainMonkeyPHPUnitIntegration;

class LogServiceProviderTest extends TestCase
{
    use BrainMonkeyPHPUnitIntegration;

    /** @test */
    public function log_object_is_always_registered()
    {
        $app = new Application(__DIR__.'/../');
        $lumberjack = new Lumberjack($app);

        $lumberjack->bootstrap();

        $this->assertTrue($app->has('logger'));
        $this->assertSame($app->get('logger'), $app->get(Logger::class));
    }

    /** @test */
    public function log_warning_level_can_be_set_in_config()
    {
        $app = new Application(__DIR__.'/../');

        $config = new Config;
        $config->set('app.log_level', Logger::ERROR);
        $app->bind('config', $config);

        $app->bootstrapWith([
            RegisterProviders::class,
        ]);

        $this->assertSame(Logger::ERROR, $app->get('logger')->getHandlers()[0]->getLevel());
    }
}
