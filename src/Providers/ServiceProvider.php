<?php

namespace Rareloop\Lumberjack\Providers;

use Rareloop\Lumberjack\Application;

abstract class ServiceProvider
{
    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }
}
