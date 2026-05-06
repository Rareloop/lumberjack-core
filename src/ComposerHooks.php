<?php

namespace Rareloop\Lumberjack;

use Rareloop\Lumberjack\Autodiscovery\DiscoveryRunner;

class ComposerHooks
{
    /**
     * Static helper for composer post-autoload-dump hook
     *
     * @codeCoverageIgnore
     * @param mixed $event
     * @return void
     */
    public static function postAutoloadDump($event): void
    {
        $vendorPath = $event->getComposer()->getConfig()->get('vendor-dir');
        $basePath = dirname($vendorPath);

        (new DiscoveryRunner())->run(new Application($basePath));
    }
}
