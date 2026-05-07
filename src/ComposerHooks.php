<?php

namespace Rareloop\Lumberjack;

use Rareloop\Lumberjack\Autodiscovery\DiscoveryRunner;

class ComposerHooks
{
    /**
     * Handle the post-autoload-dump hook.
     *
     * @param mixed $event
     * @return void
     */
    public static function postAutoloadDump($event): void
    {
        $vendorPath = $event->getComposer()->getConfig()->get('vendor-dir');

        if (file_exists($vendorPath . '/autoload.php')) {
            require_once $vendorPath . '/autoload.php';
        }

        (new DiscoveryRunner)($event);
    }
}
