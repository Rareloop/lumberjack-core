<?php

namespace Rareloop\Lumberjack;

use Illuminate\Encryption\Encrypter as IlluminateEncrypter;
use Illuminate\Contracts\Encryption\Encrypter as IlluminateEncrypterContract;
use Rareloop\Lumberjack\Contracts\Encrypter as EncrypterContract;

class Encrypter implements EncrypterContract
{
    protected $key;
    protected static $cipher = 'aes-256-cbc';
    protected $encrypter;

    public function __construct($key)
    {
        $this->encrypter = new IlluminateEncrypter($this->parseKey($key), static::$cipher);
    }

    public function encrypt($data)
    {
        return $this->encrypter->encrypt($data);
    }

    public function decrypt($data)
    {
        return $this->encrypter->decrypt($data);
    }

    protected function parseKey(string $key): string
    {
        $prefix = 'base64:';

        if (str_starts_with($key, $prefix)) {
            $key = base64_decode(str_replace($prefix, '', $key));
        }

        return $key;
    }
}
