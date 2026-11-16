<?php

namespace Rareloop\Lumberjack\Test\Providers;

use Brain\Monkey\Actions;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Config;
use Rareloop\Lumberjack\FacadeFactory;
use Rareloop\Lumberjack\Providers\SessionServiceProvider;
use Rareloop\Lumberjack\Session\SessionManager;
use Rareloop\Lumberjack\Session\Store;
use Rareloop\Lumberjack\Test\TestCase;
use Rareloop\Lumberjack\Test\Unit\Concerns\BrainMonkeyPHPUnitIntegration;
use SessionHandlerInterface;

class SessionServiceProviderTest extends TestCase
{
    use BrainMonkeyPHPUnitIntegration;

    #[Test]
    public function session_is_registered_in_container(): void
    {
        $app = new Application();
        $provider = new SessionServiceProvider($app);

        $provider->register();

        $this->assertInstanceOf(SessionManager::class, $app->get('session'));
    }

    #[Test]
    public function garbage_collection_converts_session_lifetime_from_minutes_to_seconds(): void
    {
        $app = new Application();
        FacadeFactory::setContainer($app);

        $config = new Config();
        $config->set('session.lifetime', 1440);
        $config->set('session.lottery', [100, 100]);
        $app->bind('config', $config);
        $app->bind(Config::class, $config);

        $provider = new SessionServiceProvider($app);
        $provider->register();

        $sessionManager = $app->get('session');
        $handler = Mockery::mock(SessionHandlerInterface::class);
        $handler->shouldReceive('read')->andReturn('');
        $handler->shouldReceive('gc')->with(1440 * 60)->once()->andReturn(true);

        $sessionManager->extend('file', function () use ($handler) {
            return new Store('session-name', $handler, 'session-id');
        });

        Actions\expectAdded('init')->whenHappen(function ($callback) {
            $callback();
        });

        $provider->boot();
    }

    #[Test]
    public function garbage_collection_converts_default_session_lifetime_from_minutes_to_seconds(): void
    {
        $app = new Application();
        FacadeFactory::setContainer($app);

        $config = new Config();
        $config->set('session.lottery', [100, 100]);
        $app->bind('config', $config);
        $app->bind(Config::class, $config);

        $provider = new SessionServiceProvider($app);
        $provider->register();

        $sessionManager = $app->get('session');
        $handler = Mockery::mock(SessionHandlerInterface::class);
        $handler->shouldReceive('read')->andReturn('');
        $handler->shouldReceive('gc')->with(120 * 60)->once()->andReturn(true);

        $sessionManager->extend('file', function () use ($handler) {
            return new Store('session-name', $handler, 'session-id');
        });

        Actions\expectAdded('init')->whenHappen(function ($callback) {
            $callback();
        });

        $provider->boot();
    }
}
