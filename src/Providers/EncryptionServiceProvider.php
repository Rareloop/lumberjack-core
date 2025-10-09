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
            $encryptionKey = $this->app->get('config')->get('app.key');

            $encrypter = function () use ($config) {
                $key = $config->get('app.key');
                $cipher = $config->get('app.cipher', 'aes-256-cbc');

                // Throw own exception
                // Add unit test for legacy decryption
                // test base64 and nonbase64

                return new Encrypter($this->parseKey($key), $cipher);
            };

            $this->app->bind(EncrypterContract::class, $encrypter);
            $this->app->bind('encrypter', $encrypter);
        }
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
