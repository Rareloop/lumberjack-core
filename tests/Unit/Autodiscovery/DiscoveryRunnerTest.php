<?php

namespace Rareloop\Lumberjack\Test\Unit\Autodiscovery;

use Composer\Composer;
use Composer\Config;
use Composer\IO\IOInterface;
use Composer\Package\RootPackageInterface;
use Composer\Script\Event;
use Mockery;
use PHPUnit\Framework\TestCase;
use Rareloop\Lumberjack\Autodiscovery\DiscoveryRunner;
use org\bovigo\vfs\vfsStream;

class DiscoveryRunnerTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    protected $root;
    protected $event;
    protected $composer;
    protected $config;
    protected $package;
    protected $io;

    protected function setUp(): void
    {
        parent::setUp();

        $this->root = vfsStream::setup('root', null, [
            'vendor' => [
                'composer' => [
                    'installed.json' => json_encode(['packages' => []]),
                ],
            ],
        ]);

        $this->event = Mockery::mock(Event::class);
        $this->composer = Mockery::mock(Composer::class);
        $this->config = Mockery::mock(Config::class);
        $this->package = Mockery::mock(RootPackageInterface::class);
        
        $this->io = Mockery::mock(IOInterface::class);
        $this->io->shouldIgnoreMissing();

        $this->event->shouldReceive('getComposer')->andReturn($this->composer);
        $this->event->shouldReceive('getIO')->andReturn($this->io);
        $this->composer->shouldReceive('getConfig')->andReturn($this->config);
        $this->composer->shouldReceive('getPackage')->andReturn($this->package);
        $this->config->shouldReceive('get')->with('vendor-dir')->andReturn($this->root->url() . '/vendor');
    }

    /** @test */
    public function it_can_run_the_discovery_process_with_explicit_config()
    {
        vfsStream::newDirectory('my-app/bootstrap/cache')->at($this->root);

        $this->package->shouldReceive('getExtra')->andReturn([
            'lumberjack' => [
                'theme-dir' => 'my-app',
            ],
        ]);
        $this->io->shouldNotReceive('writeError');

        (new DiscoveryRunner)($this->event);

        $this->assertTrue($this->root->hasChild('my-app/bootstrap/cache/packages.php'));
    }

    /** @test */
    public function it_can_run_the_discovery_process_using_default_bedrock_path()
    {
        vfsStream::newDirectory('web/app/themes/lumberjack/bootstrap/cache')->at($this->root);

        $this->package->shouldReceive('getExtra')->andReturn([]);
        $this->io->shouldNotReceive('writeError');

        (new DiscoveryRunner)($this->event);

        $this->assertTrue($this->root->hasChild('web/app/themes/lumberjack/bootstrap/cache/packages.php'));
    }

    /** @test */
    public function it_emits_warning_if_no_config_and_no_bedrock_path_found()
    {
        $this->package->shouldReceive('getExtra')->andReturn([]);

        $this->io->shouldReceive('writeError')->once()->with(Mockery::on(function ($message) {
            return str_contains($message, 'default path was not found') && str_contains($message, 'Package auto-discovery won\'t work');
        }));

        (new DiscoveryRunner)($this->event);

        $this->assertFalse($this->root->hasChild('bootstrap/cache/packages.php'));
    }

    /** @test */
    public function it_emits_error_if_configured_path_is_missing()
    {
        $this->package->shouldReceive('getExtra')->andReturn([
            'lumberjack' => [
                'theme-dir' => 'custom-app',
            ],
        ]);

        $this->io->shouldReceive('writeError')->once()->with(Mockery::on(function ($message) {
            return str_contains($message, 'configured theme directory') && str_contains($message, 'does not exist');
        }));

        (new DiscoveryRunner)($this->event);
    }
}
