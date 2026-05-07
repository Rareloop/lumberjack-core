<?php

namespace Rareloop\Lumberjack\Autodiscovery;

use Composer\Script\Event;
use Illuminate\Support\Arr;

class DiscoveryRunner
{
    /**
     * Run the discovery process.
     *
     * @return void
     */
    public function __invoke(Event $event): void
    {
        $composer = $event->getComposer();
        $io = $event->getIO();
        $vendorPath = $composer->getConfig()->get('vendor-dir');
        $projectPath = dirname($vendorPath);
        $extra = $composer->getPackage()->getExtra();

        try {
            $themePath = $this->resolveThemeDirectory($projectPath, $extra);

            $builder = new PackageManifest($projectPath, $vendorPath);
            $cache = new ManifestCache($themePath . DIRECTORY_SEPARATOR . 'bootstrap' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'packages.php');

            $cache->write($builder->build());
        } catch (\RuntimeException $e) {
            $io->writeError("<warning>Lumberjack: {$e->getMessage()} Package auto-discovery won't work as expected.</warning>");
        }
    }

    /**
     * Resolve the theme directory for discovery.
     *
     * @param string $projectPath
     * @param array $extra
     * @return string
     * @throws \RuntimeException
     */
    protected function resolveThemeDirectory(string $projectPath, array $extra): string
    {
        $themeDir = Arr::get($extra, 'lumberjack.theme-dir');

        if ($themeDir) {
            $path = $projectPath . DIRECTORY_SEPARATOR . $themeDir;

            if (is_dir($path)) {
                return $path;
            }

            throw new \RuntimeException("The configured theme directory \"{$path}\" does not exist.");
        }

        $defaultPath = $projectPath . DIRECTORY_SEPARATOR . 'web' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . 'lumberjack';

        if (is_dir($defaultPath)) {
            return $defaultPath;
        }

        throw new \RuntimeException('"extra.lumberjack.theme-dir" is not set in composer.json and the default path was not found.');
    }
}
