<?php

namespace Rareloop\Lumberjack\Test\Facades;

use PHPUnit\Framework\TestCase;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Config;
use Rareloop\Lumberjack\FacadeManager;
use Rareloop\Lumberjack\Facades\Config as ConfigFacade;

class ConfigTest extends TestCase
{
    /** @test */
    public function test_facade()
    {
        $app = new Application();
        FacadeManager::setContainer($app);

        $config = new Config();
        $config->set('app.environment', 'production');
        $app->bind('config', $config);

        $this->assertInstanceOf(Config::class, ConfigFacade::__instance());
        $this->assertSame('production', ConfigFacade::get('app.environment'));
    }
}
