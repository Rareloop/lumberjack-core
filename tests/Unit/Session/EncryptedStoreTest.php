<?php

namespace Rareloop\Lumberjack\Test;

use Dcrypt\AesCbc;
use Mockery;
use PHPUnit\Framework\TestCase;
use Rareloop\Lumberjack\Encrypter;
use Rareloop\Lumberjack\Exceptions\HandlerInterface;
use Rareloop\Lumberjack\Session\EncryptedStore;
use Rareloop\Lumberjack\Session\Store;
use Rareloop\Lumberjack\Test\Unit\Session\NullSessionHandler;

class EncryptedStoreTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    /** @test */
    public function data_is_encrypted_before_it_is_saved()
    {
        $encrypter = Mockery::mock(Encrypter::class);
        $encrypter->shouldReceive('encrypt')
            ->withArgs(function ($string) {
                $array = @unserialize($string);

                return $array['foo'] === 'bar';
            })
            ->once();

        $store = new EncryptedStore('session-name', new NullSessionHandler, $encrypter, 'session-id');

        $store->put('foo', 'bar');

        $store->save();
    }

    /** @test */
    public function data_is_decrypted_before_it_is_loaded()
    {
        $encryptionKey = 'base64:ydjOTEkQq2WsMwzhSgq4xuv392AdyjENcO8/VrNl37w=';

        // Use a string that has been previously been encrypted by the store with the key above. It was encrypted using the following code:
        // $encrypter = new Encrypter($encryptionKey);
        // $value = $encrypter->encrypt(@serialize(['foo' => 'bar']));
        $encryptedString = 'eyJpdiI6IlAvOURGM3FPck1PU2hQTG9ScFVaMEE9PSIsInZhbHVlIjoiRmFmdmt6ZnVrNWo5c0JaQkNnSHBMTG42czhEWVlXWGZFTnBBWTdCdzBwNUxYZkFQdU9jd21CSCtocFhSMXZ5ayIsIm1hYyI6IjY4NzhkZjFiMDAyM2Y4ODI3MzQ1MTA5YmU3MDQ5ODljYTY5ZDFjNGFiNzdkYjRjNTBlMTgxZTE0MWY2ODIxYjUiLCJ0YWciOiIifQ==';

        // Use a mock handler to fake a previously stored state
        $handler = Mockery::mock(NullSessionHandler::class . '[read]');
        $handler->shouldReceive('read')->andReturn($encryptedString);

        $store = new EncryptedStore('session-name', $handler, new Encrypter($encryptionKey), 'session-id');
        $store->start();

        $this->assertSame('bar', $store->get('foo'));
    }

    /**
     * @test
     * @dataProvider unexpectedSessionData
     */
    public function unexpected_session_data_is_handled_gracefully($previousSessionValue)
    {
        $encryptionKey = 'base64:ydjOTEkQq2WsMwzhSgq4xuv392AdyjENcO8/VrNl37w=';

        // Use a mock handler to fake a previously stored state
        $handler = Mockery::mock(NullSessionHandler::class . '[read]');
        $handler->shouldReceive('read')->andReturn($previousSessionValue);

        $errorHandler = Mockery::mock(HandlerInterface::class);
        $errorHandler->shouldReceive('report')->once();

        $store = new EncryptedStore('session-name', $handler, new Encrypter($encryptionKey), 'session-id', $errorHandler);
        $store->start();

        $this->assertSame(null, $store->get('foo'));
    }

    public function unexpectedSessionData()
    {
        return [
            [@serialize(['foo' => 'bar'])],
            [''],
        ];
    }
}
