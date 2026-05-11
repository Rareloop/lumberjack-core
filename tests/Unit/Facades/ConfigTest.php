<?php

namespace Rareloop\Lumberjack\Test\Facades;

use PHPUnit\Framework\Attributes\Test;
use Rareloop\Lumberjack\Test\TestCase;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Config;
use Rareloop\Lumberjack\FacadeFactory;
use Rareloop\Lumberjack\Facades\Config as ConfigFacade;

class ConfigTest extends TestCase
{
    #[Test]
    public function test_facade()
    {
        $app = new Application();
        FacadeFactory::setContainer($app);

        $config = new Config();
        $config->set('app.environment', 'production');
        $app->bind('config', $config);

        $this->assertInstanceOf(Config::class, ConfigFacade::__instance());
        $this->assertSame('production', ConfigFacade::get('app.environment'));
    }
}
