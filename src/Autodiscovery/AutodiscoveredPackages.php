<?php

namespace Rareloop\Lumberjack\Autodiscovery;

use Illuminate\Support\Arr;

class AutodiscoveredPackages
{
    protected ?array $manifest = null;

    public function __construct(protected ManifestCache $cache)
    {
    }

    public function providers(): array
    {
        return (array) Arr::get($this->getManifest(), 'providers', []);
    }

    public function aliases(): array
    {
        return (array) Arr::get($this->getManifest(), 'aliases', []);
    }

    protected function getManifest(): array
    {
        if ($this->manifest !== null) {
            return $this->manifest;
        }

        if ($this->cache->exists()) {
            return $this->manifest = (array) $this->cache->read();
        }

        return $this->manifest = ['providers' => [], 'aliases' => []];
    }
}
