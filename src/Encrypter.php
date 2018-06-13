<?php

namespace Rareloop\Lumberjack;

use Dcrypt\AesCbc;
use Rareloop\Lumberjack\Contracts\Encrypter as EncrypterContract;

class Encrypter implements EncrypterContract
{
    protected $key;

    public function __construct($key)
    {
        $this->key = $key;
    }

    public function encrypt($data)
    {
        return AesCbc::encrypt(@serialize($data), $this->key);
    }

    public function decrypt($data)
    {
        return @unserialize(AesCbc::decrypt($data, $this->key));
    }
}
