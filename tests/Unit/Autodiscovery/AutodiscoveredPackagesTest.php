<?php

namespace Rareloop\Lumberjack\Test\Unit\Autodiscovery;

use Mockery;
use PHPUnit\Framework\TestCase;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Autodiscovery\AutodiscoveredPackages;
use Rareloop\Lumberjack\Autodiscovery\ManifestCache;
use Rareloop\Lumberjack\Autodiscovery\PackageManifest;

class AutodiscoveredPackagesTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    protected $builder;
    protected $cache;
    protected $app;

    protected function setUp(): void
    {
        parent::setUp();
        $this->builder = Mockery::mock(PackageManifest::class);
        $this->cache = Mockery::mock(ManifestCache::class);
        $this->app = Mockery::mock(Application::class);
    }

    /** @test */
    public function it_reads_from_cache_if_not_stale()
    {
        $manifestData = ['providers' => ['Cached\Provider']];

        $this->cache->shouldReceive('exists')->andReturn(true);
        $this->builder->shouldReceive('mtime')->andReturn(100);
        $this->cache->shouldReceive('mtime')->andReturn(200);
        $this->cache->shouldReceive('read')->once()->andReturn($manifestData);

        $orchestrator = new AutodiscoveredPackages($this->builder, $this->cache, $this->app);

        $this->assertEquals(['Cached\Provider'], $orchestrator->providers());
    }

    /** @test */
    public function it_can_get_aliases()
    {
        $manifestData = ['aliases' => ['Foo' => 'Bar']];

        $this->cache->shouldReceive('exists')->andReturn(true);
        $this->builder->shouldReceive('mtime')->andReturn(100);
        $this->cache->shouldReceive('mtime')->andReturn(200);
        $this->cache->shouldReceive('read')->once()->andReturn($manifestData);

        $orchestrator = new AutodiscoveredPackages($this->builder, $this->cache, $this->app);

        $this->assertEquals(['Foo' => 'Bar'], $orchestrator->aliases());
    }

    /** @test */
    public function it_refreshes_cache_if_stale()
    {
        $manifestData = ['providers' => ['Fresh\Provider']];

        $this->cache->shouldReceive('exists')->andReturn(true);
        $this->builder->shouldReceive('mtime')->andReturn(300);
        $this->cache->shouldReceive('mtime')->andReturn(200);

        $this->builder->shouldReceive('build')->once()->andReturn($manifestData);
        $this->cache->shouldReceive('write')->once()->with($manifestData);

        $orchestrator = new AutodiscoveredPackages($this->builder, $this->cache, $this->app);

        $this->assertEquals(['Fresh\Provider'], $orchestrator->providers());
    }

    /** @test */
    public function it_refreshes_cache_if_cache_missing()
    {
        $manifestData = ['providers' => ['Fresh\Provider']];

        $this->cache->shouldReceive('exists')->andReturn(false);
        $this->builder->shouldReceive('build')->once()->andReturn($manifestData);
        $this->cache->shouldReceive('write')->once()->with($manifestData);

        $orchestrator = new AutodiscoveredPackages($this->builder, $this->cache, $this->app);

        $this->assertEquals(['Fresh\Provider'], $orchestrator->providers());
    }

    /** @test */
    public function it_logs_a_warning_if_cache_is_unwritable_and_debug_is_enabled()
    {
        $manifestData = ['providers' => ['Fresh\Provider']];

        $this->cache->shouldReceive('exists')->andReturn(false);
        $this->builder->shouldReceive('build')->andReturn($manifestData);
        $this->cache->shouldReceive('getPath')->andReturn('/path/to/cache');

        // Simulate write failure
        $this->cache->shouldReceive('write')->andThrow(new \Symfony\Component\Filesystem\Exception\IOException('Unwritable'));

        $logger = Mockery::mock(\Psr\Log\LoggerInterface::class);
        $logger->shouldReceive('warning')->once()->with("The /path/to/cache directory is not writable. Please check your permissions.");

        $this->app->shouldReceive('has')->with(\Psr\Log\LoggerInterface::class)->andReturn(true);
        $this->app->shouldReceive('get')->with(\Psr\Log\LoggerInterface::class)->andReturn($logger);

        // Explicitly set debug to true
        $orchestrator = new AutodiscoveredPackages($this->builder, $this->cache, $this->app, true);
        $orchestrator->refresh();
    }

    /** @test */
    public function it_does_not_log_a_warning_if_debug_is_disabled()
    {
        $manifestData = ['providers' => ['Fresh\Provider']];

        $this->cache->shouldReceive('exists')->andReturn(false);
        $this->builder->shouldReceive('build')->andReturn($manifestData);
        $this->cache->shouldReceive('getPath')->andReturn('/path/to/cache');

        // Simulate write failure
        $this->cache->shouldReceive('write')->andThrow(new \Symfony\Component\Filesystem\Exception\IOException('Unwritable'));

        $this->app->shouldNotReceive('has');
        $this->app->shouldNotReceive('get');

        // Explicitly set debug to false
        $orchestrator = new AutodiscoveredPackages($this->builder, $this->cache, $this->app, false);
        $orchestrator->refresh();
    }

    /** @test */
    public function it_does_not_log_if_logger_is_missing()
    {
        $manifestData = ['providers' => ['Fresh\Provider']];

        $this->cache->shouldReceive('exists')->andReturn(false);
        $this->builder->shouldReceive('build')->andReturn($manifestData);
        $this->cache->shouldReceive('getPath')->andReturn('/path/to/cache');

        // Simulate write failure
        $this->cache->shouldReceive('write')->andThrow(new \Symfony\Component\Filesystem\Exception\IOException('Unwritable'));

        $this->app->shouldReceive('has')->with(\Psr\Log\LoggerInterface::class)->andReturn(false);
        $this->app->shouldNotReceive('get');

        $orchestrator = new AutodiscoveredPackages($this->builder, $this->cache, $this->app, true);
        $orchestrator->refresh();
    }

    /** @test */
    public function manifest_is_only_loaded_once()
    {
        $manifestData = ['providers' => []];

        $this->cache->shouldReceive('exists')->andReturn(true);
        $this->builder->shouldReceive('mtime')->andReturn(100);
        $this->cache->shouldReceive('mtime')->andReturn(200);
        $this->cache->shouldReceive('read')->once()->andReturn($manifestData);

        $orchestrator = new AutodiscoveredPackages($this->builder, $this->cache, $this->app);

        $orchestrator->providers();
        $orchestrator->providers(); // Second call should not trigger 'read'
        
        // Asserting equality to trigger PHPUnit count
        $this->assertEquals([], $orchestrator->providers());
    }

    /** @test */
    public function debug_flag_is_accessible()
    {
        $orchestrator = new AutodiscoveredPackages($this->builder, $this->cache, $this->app, true);
        $this->assertTrue($orchestrator->debug);
    }
}
