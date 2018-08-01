<?php

namespace Rareloop\Lumberjack\Providers;

use Rareloop\Lumberjack\Config;

class ImageSizesServiceProvider extends ServiceProvider
{
    public function boot(Config $config)
    {
        $imageSizes = $config->get('images.sizes');

        if (is_array($imageSizes)) {
            foreach ($imageSizes as $imageSize) {
                add_image_size(
                    $imageSize['name'],
                    $imageSize['width'],
                    $imageSize['height'],
                    $imageSize['crop'] ?? false
                );
            }
        }
    }
}
