<?php

namespace Rareloop\Lumberjack\Test\Unit\Autodiscovery;

use PHPUnit\Framework\TestCase;
use Rareloop\Lumberjack\Autodiscovery\PackageManifest;
use org\bovigo\vfs\vfsStream;

class PackageManifestTest extends TestCase
{
    protected $root;

    protected function setUp(): void
    {
        parent::setUp();

        $this->root = vfsStream::setup('root', null, [
            'vendor' => [
                'composer' => [
                    'installed.json' => json_encode([
                        'packages' => [
                            [
                                'name' => 'rareloop/lumberjack-test-package',
                                'extra' => [
                                    'lumberjack' => [
                                        'providers' => [
                                            'Rareloop\Lumberjack\Validation\ValidationServiceProvider'
                                        ],
                                        'aliases' => [
                                            'test-foo' => 'Rareloop\Lumberjack\Validation\FormInterface'
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ]),
                ],
            ],
            'composer.json' => json_encode([]),
        ]);
    }

    /** @test */
    public function it_can_discover_aliases_with_hyphens()
    {
        $manifest = new PackageManifest(
            $this->root->url(),
            $this->root->url() . '/vendor'
        );

        $data = $manifest->build();

        $this->assertContains('Rareloop\Lumberjack\Validation\ValidationServiceProvider', $data['providers']);
        $this->assertArrayHasKey('test-foo', $data['aliases']);
        $this->assertEquals('Rareloop\Lumberjack\Validation\FormInterface', $data['aliases']['test-foo']);
    }
}
