<?php

namespace Rareloop\Lumberjack\Providers;

use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Contracts\Encrypter as EncrypterContract;
use Rareloop\Lumberjack\Encrypter;
use Rareloop\Lumberjack\Facades\Config;
use Rareloop\Lumberjack\Session\SessionManager;

class EncryptionServiceProvider extends ServiceProvider
{
    protected $session;

    public function register()
    {
        if ($this->app->has('config')) {
            $encryptionKey = $this->app->get('config')->get('app.key');

            $encrypter = new Encrypter($encryptionKey);

            $this->app->bind(EncrypterContract::class, $encrypter);
            $this->app->bind('encrypter', $encrypter);
        }
    }
}
