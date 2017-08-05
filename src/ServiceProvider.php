<?php

namespace Rareloop\Lumberjack;

use Rareloop\Lumberjack\Application;

interface ServiceProvider
{
    public function register(Application $app);
}
