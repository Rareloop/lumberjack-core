<?php

namespace Rareloop\Lumberjack\Providers;

use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Config;

abstract class ServiceProvider
{
    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Merge the config in the provided path into what already exists. Existing config takes
     * priority over what is found in $path.
     *
     * @param  string $path
     * @param  string $key
     * @return void
     */
    public function mergeConfigFrom($path, $key)
    {
        $existing = $this->app->get(Config::class)->get($key, []);
        $this->app->get(Config::class)->set($key, array_merge(require $path, $existing));
    }
}
