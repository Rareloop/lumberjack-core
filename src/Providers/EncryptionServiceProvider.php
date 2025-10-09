<?php

namespace Rareloop\Lumberjack\Providers;

use Rareloop\Lumberjack\Contracts\Encrypter as EncrypterContract;
use Rareloop\Lumberjack\Encrypter;

class EncryptionServiceProvider extends ServiceProvider
{
    protected $session;

    public function register()
    {
        if ($this->app->has('config')) {
            $encrypter = function () {
                $encryptionKey = $this->app->get('config')->get('app.key');

                return new Encrypter($encryptionKey);
            };

            $this->app->bind(EncrypterContract::class, $encrypter);
            $this->app->bind('encrypter', $encrypter);
        }
    }
}
