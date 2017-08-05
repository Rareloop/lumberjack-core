<?php

namespace Rareloop\Lumberjack;

use Illuminate\Support\Arr;
use Symfony\Component\Finder\Finder;

class Config
{
    private $data = [];

    public function __construct(string $path = null)
    {
        if ($path) {
            $this->load($path);
        }
    }

    public function set(string $key, $value) : Config
    {
        Arr::set($this->data, $key, $value);

        return $this;
    }

    public function get(string $key, $default = null)
    {
        return Arr::get($this->data, $key, $default);
    }

    public function load(string $path) : Config
    {
        $finder = new Finder();
        $finder->files()->in($path)->name('/\.php$/');

        foreach ($finder as $file) {
            $configData = include $file->getRealPath();

            $this->data[$file->getBasename('.php')] = $configData;
        }

        return $this;
    }
}
