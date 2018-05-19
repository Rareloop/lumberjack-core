<?php

namespace Rareloop\Lumberjack\Session;

use SessionHandlerInterface;

class FileSessionHandler implements SessionHandlerInterface
{
    protected $path;
    protected $prefix;

    public function __construct($path, $prefix = 'lumberjack_session_')
    {
        $this->path = $path;
        $this->prefix = $prefix;
    }


    public function open($savePath, $sessionName)
    {
        return true;
    }


    public function close()
    {
        return true;
    }

    public function read($sessionId)
    {
        $filepath = $this->getFilepath($sessionId);

        if (is_file($filepath)) {
            return file_get_contents($filepath);
        }

        return '';
    }


    public function write($sessionId, $data)
    {
        file_put_contents($this->getFilepath($sessionId), $data);

        return true;
    }


    public function destroy($sessionId)
    {
        $filepath = $this->getFilepath($sessionId);

        if (is_file($filepath)) {
            unlink($filepath);
        }

        return true;
    }


    public function gc($lifetime)
    {
        foreach (glob($this->path . '/' . $this->prefix . '*') as $file) {
            if (filemtime($file) + $lifetime < time() && file_exists($file)) {
                unlink($file);
            }
        }

        return true;
    }

    protected function getFilepath($sessionId)
    {
        return $this->path . '/' . $this->prefix . $sessionId;
    }
}
