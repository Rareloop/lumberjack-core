<?php

namespace Rareloop\Lumberjack\Test\Providers;

use Rareloop\Lumberjack\Test\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Config;
use Rareloop\Lumberjack\Contracts\Encrypter as EncrypterContract;
use Rareloop\Lumberjack\Encrypter;
use Rareloop\Lumberjack\Providers\EncryptionServiceProvider;
use Rareloop\Lumberjack\Test\Unit\BrainMonkeyPHPUnitIntegration;

class EncryptionServiceProviderTest extends TestCase
{
    use BrainMonkeyPHPUnitIntegration;

    #[Test]
    public function encryptor_is_registered_in_container_when_a_config_key_is_present(): void
    {
        $config = new Config;
        $config->set('app.key', 'encryption-key');

        $app = new Application();
        $app->bind('config', $config);
        $provider = new EncryptionServiceProvider($app);

        $provider->register();

        $this->assertInstanceOf(Encrypter::class, $app->get(EncrypterContract::class));
        $this->assertInstanceOf(Encrypter::class, $app->get('encrypter'));
    }
}
