<?php

namespace Rareloop\Lumberjack\Test\Unit\Autodiscovery;

use Mockery;
use PHPUnit\Framework\TestCase;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Autodiscovery\AutodiscoveredPackages;
use Rareloop\Lumberjack\Autodiscovery\DiscoveryRunner;
use Rareloop\Lumberjack\Config;
use org\bovigo\vfs\vfsStream;

class DiscoveryRunnerTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    /** @test */
    public function it_can_run_the_discovery_process()
    {
        $root = vfsStream::setup('root', null, [
            'config' => [],
        ]);

        $app = Mockery::mock(Application::class);
        $app->shouldReceive('configPath')->andReturn($root->url() . '/config');
        
        // LoadConfiguration expects these bindings to be possible
        $app->shouldReceive('bind')->with('config', Mockery::type(Config::class));
        $app->shouldReceive('bind')->with(Config::class, Mockery::type(Config::class));

        $orchestrator = Mockery::mock(AutodiscoveredPackages::class);
        $orchestrator->shouldReceive('refresh')->once();

        $app->shouldReceive('get')->with(AutodiscoveredPackages::class)->andReturn($orchestrator);

        (new DiscoveryRunner())->run($app);
    }
}
