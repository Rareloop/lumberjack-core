<?php

namespace Rareloop\Lumberjack\Providers;

use Rareloop\Lumberjack\Config;
use Rareloop\Lumberjack\Helpers;

class ImageSizesServiceProvider extends ServiceProvider
{
    public function boot(Config $config)
    {
        $imageSizes = $config->get('images.sizes');

        if (is_array($imageSizes)) {
            collect($imageSizes)
                ->map(fn($imageSize) => Helpers::invoke($imageSize))
                ->filter()
                ->each(fn($imageSize) => add_image_size(
                    $imageSize['name'],
                    $imageSize['width'],
                    $imageSize['height'],
                    $imageSize['crop'] ?? false
                ));
        }
    }
}
