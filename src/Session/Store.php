<?php

namespace Rareloop\Lumberjack\Session;

use Illuminate\Support\Arr;
use SessionHandlerInterface;

class Store
{
    protected $name;
    protected $id;
    protected $handler;

    protected $attributes = [];

    public function __construct($name, SessionHandlerInterface $handler, $id = null)
    {
        $this->setName($name);
        $this->setId($id);

        $this->handler = $handler;
    }

    public function start()
    {
        $this->loadSession();

        return $this;
    }

    protected function loadSession()
    {
        $this->attributes = array_merge($this->attributes, $this->readFromHandler());
    }

    protected function readFromHandler()
    {
        $data = $this->handler->read($this->id);
        $data = @unserialize($this->prepareForUnserialize($data));

        if ($data !== false && !is_null($data) && is_array($data)) {
            return $data;
        }

        return [];
    }

    public function save()
    {
        $this->ageFlashData();

        $this->handler->write($this->id, $this->prepareForStorage(@serialize($this->attributes)));

        return $this;
    }

    protected function prepareForStorage($data)
    {
        return $data;
    }

    protected function prepareForUnserialize($data)
    {
        return $data;
    }

    public function put($key, $value = null)
    {
        if (!is_array($key)) {
            $key = [$key => $value];
        }

        foreach ($key as $arrayKey => $arrayValue) {
            Arr::set($this->attributes, $arrayKey, $arrayValue);
        }

        return $this;
    }

    public function get($key, $default = null)
    {
        return Arr::get($this->attributes, $key, $default);
    }

    public function all()
    {
        return $this->attributes;
    }

    public function has($key)
    {
        return Arr::exists($this->attributes, $key);
    }

    public function pull($key)
    {
        return Arr::pull($this->attributes, $key);
    }

    public function push($key, $value)
    {
        $array = $this->get($key, []);

        $array[] = $value;

        $this->put($key, $array);

        return $this;
    }

    public function forget($key)
    {
        Arr::forget($this->attributes, $key);

        return $this;
    }

    public function flush()
    {
        $this->attributes = [];

        return $this;
    }

    public function flash($key = null, $value = null)
    {
        if (is_array($key)) {
            foreach ($key as $arrayKey => $arrayValue) {
                $this->flash($arrayKey, $arrayValue);
            }

            return $this;
        }

        $this->put($key, $value);

        $this->push('_flash.new', $key);

        $this->removeFromOldFlashData([$key]);

        return $this;
    }

    public function reflash()
    {
        $this->mergeNewFlashes($this->get('_flash.old', []));
        $this->put('_flash.old', []);

        return $this;
    }

    public function keep($keys = null)
    {
        $this->mergeNewFlashes($keys = is_array($keys) ? $keys : func_get_args());
        $this->removeFromOldFlashData($keys);

        return $this;
    }

    protected function mergeNewFlashes(array $keys)
    {
        $values = array_unique(array_merge($this->get('_flash.new', []), $keys));
        $this->put('_flash.new', $values);
    }

    protected function removeFromOldFlashData($keys)
    {
        $this->put('_flash.old', array_diff($this->get('_flash.old', []), $keys));
    }

    protected function ageFlashData()
    {
        $this->forget($this->get('_flash.old', []));

        $this->put('_flash.old', $this->get('_flash.new', []));

        $this->put('_flash.new', []);
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id = null)
    {
        $id = $id ?? static::random(40);

        $this->id = $id;
    }

    /**
     * Generate a more truly "random" alpha-numeric string.
     * From: https://github.com/laravel/framework/blob/5.6/src/Illuminate/Support/Str.php#L289
     *
     * @param  int  $length
     * @return string
     */
    protected static function random($length = 16)
    {
        $string = '';
        while (($len = strlen($string)) < $length) {
            $size = $length - $len;
            $bytes = random_bytes($size);
            $string .= substr(str_replace(['/', '+', '='], '', base64_encode($bytes)), 0, $size);
        }
        return $string;
    }

    public function getHandler()
    {
        return $this->handler;
    }

    public function setPreviousUrl($url)
    {
        $this->put('_previous.url', $url);
    }

    public function previousUrl()
    {
        return $this->get('_previous.url');
    }

    public function collectGarbage(int $lifetime)
    {
        return $this->handler->gc($lifetime);
    }
}
