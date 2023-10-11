<?php

namespace Rareloop\Lumberjack\Session;

use Exception;
use SessionHandlerInterface;
use Rareloop\Lumberjack\Encrypter;
use Rareloop\Lumberjack\Exceptions\HandlerInterface;

class EncryptedStore extends Store
{
    protected $encrypter;
    protected $exceptionHandler;

    public function __construct(
        $name,
        SessionHandlerInterface $handler,
        Encrypter $encrypter,
        $id = null,
        HandlerInterface $exceptionHandler = null
    ) {
        $this->encrypter = $encrypter;
        $this->exceptionHandler = $exceptionHandler;

        parent::__construct($name, $handler, $id);
    }

    protected function prepareForStorage($data)
    {
        return $this->encrypter->encrypt($data);
    }

    protected function prepareForUnserialize($data)
    {
        try {
            return $this->encrypter->decrypt($data);
        } catch (Exception $e) {
            $this->exceptionHandler->report($e);
            return '';
        }
    }
}
