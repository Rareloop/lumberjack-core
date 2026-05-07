<?php

namespace Rareloop\Lumberjack\Autodiscovery;

use Illuminate\Support\Arr;
use Symfony\Component\Filesystem\Filesystem;

class PackageManifest
{
    public function __construct(
        protected Filesystem $filesystem,
        protected string $basePath,
        protected string $vendorPath
    ) {
    }

    /**
     * Build the manifest of autodiscovered packages.
     *
     * @return array
     */
    public function build(): array
    {
        $packages = $this->getInstalledPackages();
        $ignore = $this->getPackagesToIgnore();

        $providers = [];
        $aliases = [];

        foreach ($packages as $package) {
            $name = Arr::get($package, 'name');

            if ($this->shouldIgnore($name, $ignore)) {
                continue;
            }

            $extra = Arr::get($package, 'extra.lumberjack', []);

            if (!is_array($extra) || empty($extra)) {
                continue;
            }

            $packageProviders = Arr::get($extra, 'providers', []);
            $packageAliases = Arr::get($extra, 'aliases', []);

            if (is_array($packageProviders)) {
                foreach ($packageProviders as $provider) {
                    $providers[] = $this->formatClassName($provider);
                }
            }

            if (is_array($packageAliases)) {
                foreach ($packageAliases as $alias => $className) {
                    $aliases[$alias] = $this->formatClassName($className);
                }
            }
        }

        return [
            'providers' => array_values(array_unique($providers)),
            'aliases' => $aliases,
        ];
    }

    /**
     * Format a class name, stripping accidental '::class' suffixes.
     *
     * @param mixed $className
     * @return string
     */
    protected function formatClassName(mixed $className): string
    {
        return str_replace('::class', '', (string) $className);
    }

    public function mtime(): int
    {
        $path = $this->vendorPath . DIRECTORY_SEPARATOR . 'composer' . DIRECTORY_SEPARATOR . 'installed.json';

        return $this->filesystem->exists($path) ? filemtime($path) : 0;
    }

    protected function getInstalledPackages(): array
    {
        $path = $this->vendorPath . DIRECTORY_SEPARATOR . 'composer' . DIRECTORY_SEPARATOR . 'installed.json';

        if (!$this->filesystem->exists($path)) {
            return [];
        }

        $installed = json_decode(file_get_contents($path), true);

        if (!is_array($installed)) {
            return [];
        }

        $packages = $installed['packages'] ?? $installed;

        return is_array($packages) ? $packages : [];
    }

    protected function getPackagesToIgnore(): array
    {
        $path = $this->basePath . DIRECTORY_SEPARATOR . 'composer.json';

        if (!$this->filesystem->exists($path)) {
            return [];
        }

        $composer = json_decode(file_get_contents($path), true);

        if (!is_array($composer)) {
            return [];
        }

        $ignore = Arr::get($composer, 'extra.lumberjack.dont-discover', []);

        return is_array($ignore) ? $ignore : [];
    }

    protected function shouldIgnore(?string $name, array $ignore): bool
    {
        return $name !== null && (in_array($name, $ignore) || in_array('*', $ignore));
    }
}
