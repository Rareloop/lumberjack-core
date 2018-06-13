<?php

namespace Rareloop\Lumberjack\Test;

use Dcrypt\AesCbc;
use Mockery;
use PHPUnit\Framework\TestCase;
use Rareloop\Lumberjack\Encrypter;
use Rareloop\Lumberjack\Session\EncryptedStore;
use Rareloop\Lumberjack\Session\Store;
use Rareloop\Lumberjack\Test\Unit\Session\NullSessionHandler;

class EncyptedStoreTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    /** @test */
    public function data_is_encrypted_before_it_is_saved()
    {
        $serializedString = @serialize(['foo' => 'bar']);
        $encrypter = Mockery::mock(Encrypter::class.'[encrypt]', ['encryption-key']);
        $encrypter->shouldReceive('encrypt')->withArgs(function ($string) {
            $array = @unserialize($string);

            return $array['foo'] === 'bar';
        })->once();

        $store = new EncryptedStore('session-name', new NullSessionHandler, $encrypter, 'session-id');

        $store->put('foo', 'bar');

        $store->save();
    }

    /** @test */
    public function data_is_decrypted_before_it_is_loaded()
    {
        $encryptionKey = 'encryption-key';

        // Create the string that would have been stored by an encrypted store
        // Serialize once for the Encrypter and once for the Encrypted store
        $encryptedString = AesCbc::encrypt(@serialize(@serialize(['foo' => 'bar'])), $encryptionKey);

        // Use a mock handler to fake a previously stored state
        $handler = Mockery::mock(NullSessionHandler::class.'[read]');
        $handler->shouldReceive('read')->andReturn($encryptedString);

        $store = new EncryptedStore('session-name', $handler, new Encrypter($encryptionKey), 'session-id');
        $store->start();

        $this->assertSame('bar', $store->get('foo'));
    }
}
