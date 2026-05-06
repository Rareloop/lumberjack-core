<?php

namespace Rareloop\Lumberjack\Autodiscovery;

use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Bootstrappers\LoadConfiguration;

class DiscoveryRunner
{
    /**
     * Run the discovery process
     *
     * @param Application $app
     * @return void
     */
    public function run(Application $app): void
    {
        // Explicitly load configuration as it won't have been loaded by default
        (new LoadConfiguration)->bootstrap($app);

        $app->get(AutodiscoveredPackages::class)->refresh();
    }
}
