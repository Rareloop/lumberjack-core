<?php

namespace Rareloop\Lumberjack\Test\Unit\Autodiscovery;

use Mockery;
use PHPUnit\Framework\TestCase;
use Rareloop\Lumberjack\Autodiscovery\AutodiscoveredPackages;
use Rareloop\Lumberjack\Autodiscovery\ManifestCache;

class AutodiscoveredPackagesTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    protected $cache;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cache = Mockery::mock(ManifestCache::class);
    }

    /** @test */
    public function it_reads_from_cache_if_it_exists()
    {
        $manifestData = ['providers' => ['Cached\Provider']];

        $this->cache->shouldReceive('exists')->andReturn(true);
        $this->cache->shouldReceive('read')->once()->andReturn($manifestData);

        $orchestrator = new AutodiscoveredPackages($this->cache);

        $this->assertEquals(['Cached\Provider'], $orchestrator->providers());
    }

    /** @test */
    public function it_can_get_aliases()
    {
        $manifestData = ['aliases' => ['Foo' => 'Bar']];

        $this->cache->shouldReceive('exists')->andReturn(true);
        $this->cache->shouldReceive('read')->once()->andReturn($manifestData);

        $orchestrator = new AutodiscoveredPackages($this->cache);

        $this->assertEquals(['Foo' => 'Bar'], $orchestrator->aliases());
    }

    /** @test */
    public function it_returns_empty_arrays_if_cache_missing()
    {
        $this->cache->shouldReceive('exists')->andReturn(false);

        $orchestrator = new AutodiscoveredPackages($this->cache);

        $this->assertEquals([], $orchestrator->providers());
        $this->assertEquals([], $orchestrator->aliases());
    }

    /** @test */
    public function manifest_is_only_loaded_once()
    {
        $manifestData = ['providers' => []];

        $this->cache->shouldReceive('exists')->andReturn(true);
        $this->cache->shouldReceive('read')->once()->andReturn($manifestData);

        $orchestrator = new AutodiscoveredPackages($this->cache);

        $orchestrator->providers();
        $orchestrator->providers(); // Second call should not trigger 'read'
        
        $this->assertEquals([], $orchestrator->providers());
    }

    /** @test */
    public function providers_always_returns_an_array_even_if_manifest_corrupted()
    {
        $this->cache->shouldReceive('exists')->andReturn(true);
        $this->cache->shouldReceive('read')->andReturn(['providers' => null]);

        $orchestrator = new AutodiscoveredPackages($this->cache);

        $this->assertEquals([], $orchestrator->providers());
    }

    /** @test */
    public function aliases_always_returns_an_array_even_if_manifest_corrupted()
    {
        $this->cache->shouldReceive('exists')->andReturn(true);
        $this->cache->shouldReceive('read')->andReturn(['aliases' => null]);

        $orchestrator = new AutodiscoveredPackages($this->cache);

        $this->assertEquals([], $orchestrator->aliases());
    }
}
