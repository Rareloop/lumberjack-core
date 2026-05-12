<?php

namespace Rareloop\Lumberjack\Test\Providers;

use PHPUnit\Framework\Attributes\Test;
use Rareloop\Lumberjack\Test\TestCase;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Providers\SessionServiceProvider;
use Rareloop\Lumberjack\Session\SessionManager;
use Rareloop\Lumberjack\Test\Unit\Concerns\BrainMonkeyPHPUnitIntegration;

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
}
