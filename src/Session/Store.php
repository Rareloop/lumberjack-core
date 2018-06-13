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
    }

    protected function loadSession()
    {
        $this->attributes = array_merge($this->attributes, $this->readFromHandler());
    }

    protected function readFromHandler()
    {
        $data = $this->handler->read($this->id);
        $data = @unserialize($this->prepareForUnserialize($data));

        if ($data !== false && ! is_null($data) && is_array($data)) {
            return $data;
        }

        return [];
    }

    public function save()
    {
        $this->ageFlashData();

        $this->handler->write($this->id, $this->prepareForStorage(@serialize($this->attributes)));
    }

    protected function prepareForStorage($data)
    {
        return $data;
    }

    protected function prepareForUnserialize($data)
    {
        return $data;
    }

    public function put($key, $value)
    {
        Arr::set($this->attributes, $key, $value);
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
    }

    public function forget($key)
    {
        Arr::forget($this->attributes, $key);
    }

    public function flash($key, $value)
    {
        $this->put($key, $value);

        $this->push('_flash.new', $key);

        $this->removeFromOldFlashData([$key]);
    }

    public function reflash()
    {
        $this->mergeNewFlashes($this->get('_flash.old', []));
        $this->put('_flash.old', []);
    }

    public function keep($keys = null)
    {
        $this->mergeNewFlashes($keys = is_array($keys) ? $keys : func_get_args());
        $this->removeFromOldFlashData($keys);
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
        $id = $id ?? uniqid();

        $this->id = $id;
    }

    public function getHandler()
    {
        return $this->handler;
    }
}
