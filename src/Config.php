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

    public function has(string $key)
    {
        return Arr::has($this->data, $key);
    }

    public function load(string $path) : Config
    {
        $files = glob($path . '/*.php');

        foreach ($files as $file) {
            $configData = include $file;

            $this->data[pathinfo($file)['filename']] = $configData;
        }

        return $this;
    }
}
