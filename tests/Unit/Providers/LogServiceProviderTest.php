<?php

namespace Rareloop\Lumberjack\Test\Providers;

use Brain\Monkey\Functions;
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
        Functions\expect('is_admin')->once()->andReturn(false);

        $app = new Application(__DIR__.'/../');
        $lumberjack = new Lumberjack($app);

        $lumberjack->bootstrap();

        $this->assertTrue($app->has('logger'));
        $this->assertSame($app->get('logger'), $app->get(Logger::class));
    }

    /**
     * @test
     * @codingStandardsIgnoreLine */
    function default_handler_is_in_memory_stream()
    {
        $app = new Application(__DIR__.'/../');

        $config = new Config;
        $config->set('app.logs.level', Logger::ERROR);
        $app->bind('config', $config);

        $app->bootstrapWith([
            RegisterProviders::class,
        ]);

        $this->assertSame('php://memory', $app->get('logger')->getHandlers()[0]->getUrl());
    }

    /** @test */
    public function log_warning_level_can_be_set_in_config()
    {
        $app = new Application(__DIR__.'/../');

        $config = new Config;
        $config->set('app.logs.level', Logger::ERROR);
        $app->bind('config', $config);

        $app->bootstrapWith([
            RegisterProviders::class,
        ]);

        $this->assertSame(Logger::ERROR, $app->get('logger')->getHandlers()[0]->getLevel());
    }

    /** @test */
    public function logs_path_can_be_changed_by_config_variable()
    {
        $app = new Application('/base/path');

        $config = new Config;
        $config->set('app.logs.enabled', true);
        $config->set('app.logs.path', '/base/new.log');
        $app->bind('config', $config);

        $app->bootstrapWith([
            RegisterProviders::class,
        ]);

        $this->assertSame('/base/new.log', $app->get('logger')->getHandlers()[0]->getUrl());
    }
}
