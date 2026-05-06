<?php

namespace Rareloop\Lumberjack\Test\Unit\Autodiscovery;

use PHPUnit\Framework\TestCase;
use Rareloop\Lumberjack\Autodiscovery\ManifestCache;
use Symfony\Component\Filesystem\Filesystem;
use org\bovigo\vfs\vfsStream;

class ManifestCacheTest extends TestCase
{
    protected $root;
    protected $filesystem;

    protected function setUp(): void
    {
        parent::setUp();
        $this->root = vfsStream::setup('root');
        $this->filesystem = new Filesystem();
    }

    /** @test */
    public function it_can_check_if_cache_exists()
    {
        $cachePath = $this->root->url() . '/packages.php';
        $cache = new ManifestCache($this->filesystem, $cachePath);

        $this->assertFalse($cache->exists());

        file_put_contents($cachePath, '<?php return [];');
        $this->assertTrue($cache->exists());
    }

    /** @test */
    public function it_can_write_the_cache()
    {
        $cachePath = $this->root->url() . '/packages.php';
        $cache = new ManifestCache($this->filesystem, $cachePath);

        $manifest = ['providers' => ['Foo\Bar']];
        $cache->write($manifest);

        $this->assertTrue($this->root->hasChild('packages.php'));
        $data = require $cachePath;
        $this->assertEquals($manifest, $data);
    }

    /** @test */
    public function it_can_read_the_cache()
    {
        $cachePath = $this->root->url() . '/packages.php';
        $cache = new ManifestCache($this->filesystem, $cachePath);

        $manifest = ['providers' => ['Foo\Bar']];
        file_put_contents($cachePath, '<?php return ' . var_export($manifest, true) . ';');

        $this->assertEquals($manifest, $cache->read());
    }

    /** @test */
    public function it_returns_empty_array_if_cache_is_malformed()
    {
        $cachePath = $this->root->url() . '/packages.php';
        $cache = new ManifestCache($this->filesystem, $cachePath);

        file_put_contents($cachePath, '<?php return "not-an-array";');

        $this->assertEquals([], $cache->read());
    }

    /** @test */
    public function it_can_get_the_mtime()
    {
        $cachePath = $this->root->url() . '/packages.php';
        $cache = new ManifestCache($this->filesystem, $cachePath);

        $this->assertEquals(0, $cache->mtime());

        file_put_contents($cachePath, '<?php return [];');
        $this->assertGreaterThan(0, $cache->mtime());
    }

    /** @test */
    public function it_can_get_the_path()
    {
        $cachePath = '/path/to/cache.php';
        $cache = new ManifestCache($this->filesystem, $cachePath);

        $this->assertEquals($cachePath, $cache->getPath());
    }
}
