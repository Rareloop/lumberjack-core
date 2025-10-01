<?php

namespace Rareloop\Lumberjack\Providers;

use Illuminate\Contracts\Encryption\Encrypter as EncrypterContract;
use Illuminate\Encryption\Encrypter;

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
