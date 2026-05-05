<?php

namespace Rareloop\Lumberjack\Test\Providers;

use Brain\Monkey\Functions;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Level;
use Monolog\Logger;
use PHPUnit\Framework\Attributes\Test;
use Rareloop\Lumberjack\Test\TestCase;
use Psr\Log\LoggerInterface;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Bootstrappers\RegisterProviders;
use Rareloop\Lumberjack\Config;
use Rareloop\Lumberjack\Http\Lumberjack;
use Rareloop\Lumberjack\Test\Unit\BrainMonkeyPHPUnitIntegration;

class LogServiceProviderTest extends TestCase
{
    use BrainMonkeyPHPUnitIntegration;

    #[Test]
    public function log_object_is_always_registered(): void
    {
        Functions\expect('is_admin')->once()->andReturn(false);

        $app = new Application(__DIR__ . '/../');
        $lumberjack = new Lumberjack($app);

        $lumberjack->bootstrap();

        $this->assertTrue($app->has('logger'));
        $this->assertSame($app->get('logger'), $app->get(Logger::class));
        $this->assertSame($app->get('logger'), $app->get(LoggerInterface::class));
    }

    #[Test]
    public function default_handler_is_in_memory_stream(): void
    {
        $app = new Application(__DIR__ . '/../');

        $config = new Config;
        $app->bind('config', $config);

        $app->bootstrapWith([
            RegisterProviders::class,
        ]);

        $this->assertSame('php://memory', $app->get('logger')->getHandlers()[0]->getUrl());
    }

    #[Test]
    public function default_log_warning_level_is_debug(): void
    {
        $app = new Application(__DIR__ . '/../');

        $config = new Config;
        $app->bind('config', $config);

        $app->bootstrapWith([
            RegisterProviders::class,
        ]);

        $this->assertSame(Level::Debug, $app->get('logger')->getHandlers()[0]->getLevel());
    }

    #[Test]
    public function stream_is_used_when_path_is_set_but_logging_is_disabled(): void
    {
        $app = new Application(__DIR__ . '/../');

        $config = new Config;
        $config->set('app.logs.enabled', false);
        $config->set('app.logs.path', 'app.log');
        $app->bind('config', $config);

        $app->bootstrapWith([
            RegisterProviders::class,
        ]);

        $this->assertSame('php://memory', $app->get('logger')->getHandlers()[0]->getUrl());
    }

    #[Test]
    public function log_warning_level_can_be_set_in_config(): void
    {
        $app = new Application(__DIR__ . '/../');

        $config = new Config;
        $config->set('app.logs.level', Level::Error);
        $app->bind('config', $config);

        $app->bootstrapWith([
            RegisterProviders::class,
        ]);

        $this->assertSame(Level::Error, $app->get('logger')->getHandlers()[0]->getLevel());
    }

    #[Test]
    public function error_log_is_used_when_path_is_set_to_false(): void
    {
        $app = new Application('/base/path');

        $config = new Config;
        $config->set('app.logs.enabled', true);
        $config->set('app.logs.path', false);
        $app->bind('config', $config);

        $app->bootstrapWith([
            RegisterProviders::class,
        ]);

        $this->assertInstanceOf(ErrorLogHandler::class, $app->get('logger')->getHandlers()[0]);
    }

    #[Test]
    public function stream_is_used_when_path_is_set_to_false_and_enabled_is_false(): void
    {
        $app = new Application('/base/path');

        $config = new Config;
        $config->set('app.logs.enabled', false);
        $config->set('app.logs.path', false);
        $app->bind('config', $config);

        $app->bootstrapWith([
            RegisterProviders::class,
        ]);

        $this->assertSame('php://memory', $app->get('logger')->getHandlers()[0]->getUrl());
    }

    #[Test]
    public function logs_path_can_be_changed_by_config_variable(): void
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
