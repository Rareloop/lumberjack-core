<?php

namespace Rareloop\Lumberjack\Autodiscovery;

use Illuminate\Support\Arr;
use Psr\Log\LoggerInterface;
use Rareloop\Lumberjack\Application;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

class AutodiscoveredPackages
{
    protected ?array $manifest = null;

    public function __construct(
        protected PackageManifest $builder,
        protected ManifestCache $cache,
        protected Application $app,
        public readonly bool $debug = false
    ) {
    }

    public function providers(): array
    {
        return Arr::get($this->getManifest(), 'providers', []);
    }

    public function aliases(): array
    {
        return Arr::get($this->getManifest(), 'aliases', []);
    }

    protected function getManifest(): array
    {
        if ($this->manifest !== null) {
            return $this->manifest;
        }

        if (!$this->isStale() && $this->cache->exists()) {
            return $this->manifest = $this->cache->read();
        }

        return $this->manifest = $this->refresh();
    }

    protected function isStale(): bool
    {
        if (!$this->cache->exists()) {
            return true;
        }

        return $this->builder->mtime() > $this->cache->mtime();
    }

    public function refresh(): array
    {
        $manifest = $this->builder->build();

        try {
            $this->cache->write($manifest);
        } catch (IOExceptionInterface $e) {
            $this->warn("The {$this->cache->getPath()} directory is not writable. Please check your permissions.");
        }

        return $manifest;
    }

    protected function warn(string $message): void
    {
        if ($this->debug) {
            if ($this->app->has(LoggerInterface::class)) {
                $this->app->get(LoggerInterface::class)->warning($message);
            }
        }
    }
}
