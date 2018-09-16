<?php

namespace Rareloop\Lumberjack\Test\Providers;

use PHPUnit\Framework\TestCase;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Providers\SessionServiceProvider;
use Rareloop\Lumberjack\Session\SessionManager;
use Rareloop\Lumberjack\Test\Unit\BrainMonkeyPHPUnitIntegration;

class SessionServiceProviderTest extends TestCase
{
    use BrainMonkeyPHPUnitIntegration;

    /** @test */
    public function session_is_registered_in_container()
    {
        $app = new Application();
        $provider = new SessionServiceProvider($app);

        $provider->register();

        $this->assertInstanceOf(SessionManager::class, $app->get('session'));
    }
}
