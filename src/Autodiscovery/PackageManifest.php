<?php

namespace Rareloop\Lumberjack\Autodiscovery;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Symfony\Component\Filesystem\Filesystem;

class PackageManifest
{
    public function __construct(
        protected Filesystem $filesystem,
        protected string $basePath,
        protected string $vendorPath
    ) {
    }

    public function build(): array
    {
        $packages = $this->getInstalledPackages();
        $ignore = $this->getPackagesToIgnore();

        return Collection::make($packages)
            ->mapWithKeys(fn ($package) => [Arr::get($package, 'name') => Arr::get($package, 'extra.lumberjack', [])])
            ->reject(fn ($extra, $name) => $this->shouldIgnore($name, $ignore))
            ->filter()
            ->reduce(function ($carry, $extra) {
                return [
                    'providers' => array_merge(Arr::get($carry, 'providers', []), Arr::get($extra, 'providers', [])),
                    'aliases' => array_merge(Arr::get($carry, 'aliases', []), Arr::get($extra, 'aliases', [])),
                ];
            }, ['providers' => [], 'aliases' => []]);
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

        return Arr::get($installed, 'packages', $installed);
    }

    protected function getPackagesToIgnore(): array
    {
        $path = $this->basePath . DIRECTORY_SEPARATOR . 'composer.json';

        if (!$this->filesystem->exists($path)) {
            return [];
        }

        $composer = json_decode(file_get_contents($path), true);

        return Arr::get($composer, 'extra.lumberjack.dont-discover', []);
    }

    protected function shouldIgnore(string $name, array $ignore): bool
    {
        return in_array($name, $ignore) || in_array('*', $ignore);
    }
}
