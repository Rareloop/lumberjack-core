<?php

namespace Rareloop\Lumberjack\Session;

use Rareloop\Lumberjack\Encrypter;
use SessionHandlerInterface;

class EncryptedStore extends Store
{
    protected $encrypter;

    public function __construct($name, SessionHandlerInterface $handler, Encrypter $encrypter, $id = null)
    {
        $this->encrypter = $encrypter;

        parent::__construct($name, $handler, $id);
    }

    protected function prepareForStorage($data)
    {
        return $this->encrypter->encrypt($data);
    }

    protected function prepareForUnserialize($data)
    {
        return $this->encrypter->decrypt($data);
    }
}
