<?php

namespace Rareloop\Lumberjack\Test\Unit\Session;

use SessionHandlerInterface;

class NullSessionHandler implements SessionHandlerInterface
{
    #[\ReturnTypeWillChange]
    public function open($savePath, $sessionName)
    {
        return true;
    }

    #[\ReturnTypeWillChange]
    public function close()
    {
        return true;
    }

    #[\ReturnTypeWillChange]
    public function read($sessionId)
    {
        return '';
    }

    #[\ReturnTypeWillChange]
    public function write($sessionId, $data)
    {
        return true;
    }

    #[\ReturnTypeWillChange]
    public function destroy($sessionId)
    {
        return true;
    }

    #[\ReturnTypeWillChange]
    public function gc($lifetime)
    {
        return true;
    }
}
