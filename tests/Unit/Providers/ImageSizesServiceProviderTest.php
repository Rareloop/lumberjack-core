<?php

namespace Rareloop\Lumberjack\Test;

use Brain\Monkey\Functions;
use Mockery;
use PHPUnit\Framework\TestCase;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Config;
use Rareloop\Lumberjack\Providers\ImageSizesServiceProvider;
use Rareloop\Lumberjack\Test\Unit\BrainMonkeyPHPUnitIntegration;

class ImageSizesTest extends TestCase
{
    use BrainMonkeyPHPUnitIntegration;

    /** @test */
    public function single_image_size_should_be_set_from_config()
    {
        $app = new Application(__DIR__ . '/..');
        $config = new Config;

        $config->set('images.sizes', [
            [
                'name' => 'thumbnail',
                'width' => 100,
                'height' => 200,
                'crop' => true,
            ]
        ]);

        Functions\expect('add_image_size')
            ->once()
            ->with('thumbnail', 100, 200, true);

        $provider = new ImageSizesServiceProvider($app);
        $provider->boot($config);
    }

    /** @test */
    public function crop_should_be_false_if_not_set()
    {
        $app = new Application(__DIR__ . '/..');
        $config = new Config;

        $config->set('images.sizes', [
            [
                'name' => 'thumbnail',
                'width' => 100,
                'height' => 200,
            ]
        ]);

        Functions\expect('add_image_size')
            ->once()
            ->with('thumbnail', 100, 200, false);

        $provider = new ImageSizesServiceProvider($app);
        $provider->boot($config);
    }

    /** @test */
    public function multiple_image_sizes_should_be_set_from_config()
    {
        $app = new Application(__DIR__ . '/..');
        $config = new Config;

        $config->set('images.sizes', [
            [
                'name' => 'thumbnail',
                'width' => 100,
                'height' => 200,
                'crop' => true,
            ],
            [
                'name' => 'full',
                'width' => 300,
                'height' => 600,
                'crop' => false,
            ]
        ]);

        Functions\expect('add_image_size')
            ->times(2)
            ->with(Mockery::type('string'), Mockery::type('int'), Mockery::type('int'), Mockery::type('bool'));

        $provider = new ImageSizesServiceProvider($app);
        $provider->boot($config);
    }
}
