<?php

namespace Rareloop\Lumberjack;

use Illuminate\Encryption\Encrypter as IlluminateEncrypter;
use Rareloop\Lumberjack\Contracts\Encrypter as EncrypterContract;
use Rareloop\Lumberjack\Exceptions\DecryptException;
use Rareloop\Lumberjack\Exceptions\EncryptException;
use Rareloop\Lumberjack\Exceptions\InvalidEncryptionKeyException;

class Encrypter implements EncrypterContract
{
    protected $key;
    protected static $cipher = 'aes-256-cbc';
    protected $encrypter;

    public function __construct($key)
    {
        try {
            $this->encrypter = new IlluminateEncrypter($this->parseKey($key), static::$cipher);
        } catch (\Throwable $th) {
            throw new InvalidEncryptionKeyException;
        }
    }

    public function encrypt($data)
    {
        try {
            return $this->encrypter->encrypt($data);
        } catch (\Throwable $th) {
            throw new EncryptException('Unable to encrypt the data.');
        }
    }

    public function decrypt($data)
    {
        try {
            return $this->encrypter->decrypt($data);
        } catch (\Throwable $th) {
            throw new DecryptException('Unable to decrypt the data: ' . $th->getMessage());
        }
    }

    public static function generateKey()
    {
        return IlluminateEncrypter::generateKey(static::$cipher);
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
