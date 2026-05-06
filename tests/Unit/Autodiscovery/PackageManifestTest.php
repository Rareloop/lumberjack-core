<?php

namespace Rareloop\Lumberjack\Test\Unit\Autodiscovery;

use PHPUnit\Framework\TestCase;
use Rareloop\Lumberjack\Autodiscovery\PackageManifest;
use Symfony\Component\Filesystem\Filesystem;
use org\bovigo\vfs\vfsStream;

class PackageManifestTest extends TestCase
{
    protected $root;
    protected $filesystem;

    protected function setUp(): void
    {
        parent::setUp();

        $this->root = vfsStream::setup('root', null, [
            'vendor' => [
                'composer' => [
                    'installed.json' => json_encode([
                        'packages' => [
                            [
                                'name' => 'package/one',
                                'extra' => [
                                    'lumberjack' => [
                                        'providers' => ['Package\One\ServiceProvider'],
                                        'aliases' => ['One' => 'Package\One\Facade'],
                                    ],
                                ],
                            ],
                        ],
                    ]),
                ],
            ],
            'composer.json' => json_encode([]),
        ]);

        $this->filesystem = new Filesystem();
    }

    /** @test */
    public function it_can_build_the_manifest()
    {
        $manifest = new PackageManifest(
            $this->filesystem,
            $this->root->url(),
            $this->root->url() . '/vendor'
        );

        $data = $manifest->build();

        $this->assertEquals(['Package\One\ServiceProvider'], $data['providers']);
        $this->assertEquals(['One' => 'Package\One\Facade'], $data['aliases']);
    }

    /** @test */
    public function it_respects_dont_discover()
    {
        $this->root->getChild('composer.json')->setContent(json_encode([
            'extra' => [
                'lumberjack' => [
                    'dont-discover' => ['package/one'],
                ],
            ],
        ]));

        $manifest = new PackageManifest(
            $this->filesystem,
            $this->root->url(),
            $this->root->url() . '/vendor'
        );

        $data = $manifest->build();

        $this->assertEmpty($data['providers']);
        $this->assertEmpty($data['aliases']);
    }

    /** @test */
    public function it_respects_wildcard_dont_discover()
    {
        $this->root->getChild('composer.json')->setContent(json_encode([
            'extra' => [
                'lumberjack' => [
                    'dont-discover' => ['*'],
                ],
            ],
        ]));

        $manifest = new PackageManifest(
            $this->filesystem,
            $this->root->url(),
            $this->root->url() . '/vendor'
        );

        $data = $manifest->build();

        $this->assertEmpty($data['providers']);
    }

    /** @test */
    public function it_can_get_the_mtime_of_installed_json()
    {
        $manifest = new PackageManifest(
            $this->filesystem,
            $this->root->url(),
            $this->root->url() . '/vendor'
        );

        $this->assertGreaterThan(0, $manifest->mtime());
    }

    /** @test */
    public function it_returns_zero_mtime_if_installed_json_missing()
    {
        $this->root->getChild('vendor/composer')->removeChild('installed.json');

        $manifest = new PackageManifest(
            $this->filesystem,
            $this->root->url(),
            $this->root->url() . '/vendor'
        );

        $this->assertEquals(0, $manifest->mtime());
    }

    /** @test */
    public function it_returns_empty_packages_if_installed_json_missing()
    {
        $this->root->getChild('vendor/composer')->removeChild('installed.json');

        $manifest = new PackageManifest(
            $this->filesystem,
            $this->root->url(),
            $this->root->url() . '/vendor'
        );

        $data = $manifest->build();
        $this->assertEmpty($data['providers']);
    }

    /** @test */
    public function it_returns_empty_ignore_list_if_composer_json_missing()
    {
        $this->root->removeChild('composer.json');

        $manifest = new PackageManifest(
            $this->filesystem,
            $this->root->url(),
            $this->root->url() . '/vendor'
        );

        $data = $manifest->build();
        $this->assertEquals(['Package\One\ServiceProvider'], $data['providers']);
    }
}
