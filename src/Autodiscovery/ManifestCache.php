<?php

namespace Rareloop\Lumberjack\Autodiscovery;

use Symfony\Component\Filesystem\Filesystem;

class ManifestCache
{
    public function __construct(
        protected Filesystem $filesystem,
        protected string $cachePath
    ) {
    }

    public function exists(): bool
    {
        return $this->filesystem->exists($this->cachePath);
    }

    public function read(): array
    {
        $data = require $this->cachePath;

        return is_array($data) ? $data : [];
    }

    public function write(array $manifest): void
    {
        $this->filesystem->dumpFile(
            $this->cachePath,
            '<?php return ' . var_export($manifest, true) . ';'
        );
    }

    public function mtime(): int
    {
        return $this->exists() ? filemtime($this->cachePath) : 0;
    }

    public function getPath(): string
    {
        return $this->cachePath;
    }
}
