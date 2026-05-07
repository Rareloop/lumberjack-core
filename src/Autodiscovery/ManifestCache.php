<?php

namespace Rareloop\Lumberjack\Autodiscovery;

class ManifestCache
{
    public function __construct(protected string $cachePath)
    {
    }

    public function exists(): bool
    {
        return file_exists($this->cachePath);
    }

    public function read(): array
    {
        $data = require $this->cachePath;

        return is_array($data) ? $data : [];
    }

    public function write(array $manifest): void
    {
        $directory = dirname($this->cachePath);

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        file_put_contents(
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
