<?php

namespace Rareloop\Lumberjack\Test;

use Mockery;
use ReflectionProperty;
use PHPUnit\Framework\TestCase;
use Rareloop\Lumberjack\Encrypter;
use Illuminate\Encryption\Encrypter as IlluminateEncrypter;

/**
 * @preserveGlobalState disabled
 */
class EncrypterTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    /** @test */
    public function can_encrypt_data()
    {
        $key = 'YurEU4attIXDGJWTL5VNvEXkMTosdBah';

        $illuminateMock = Mockery::mock(IlluminateEncrypter::class);
        $illuminateMock
            ->shouldReceive('encrypt')
            ->with('test-string')
            ->once();

        $encrypter = new Encrypter($key);

        // Replace the internal Illuminate encrypter
        $ref = new ReflectionProperty($encrypter, 'encrypter');
        $ref->setAccessible(true);
        $ref->setValue($encrypter, $illuminateMock);

        $encrypter->encrypt('test-string');
    }

    /** @test */
    public function can_decrypt_data()
    {
        $key = 'secret-key';
        $dcrypt = Mockery::mock('alias:' . AesCbc::class);
        $dcrypt->shouldReceive('decrypt')->with('test-string', $key)->once();

        $encrypter = new Encrypter($key);
        $encrypter->decrypt('test-string');
    }

    /** @test */
    public function can_process_strings()
    {
        $payload = 'test-string';
        $encrypter = new Encrypter('secret-key');

        $this->assertSame($payload, $encrypter->decrypt($encrypter->encrypt($payload)));
    }

    /** @test */
    public function can_process_arrays()
    {
        $payload = ['foo' => 'bar'];
        $encrypter = new Encrypter('secret-key');

        $this->assertSame($payload, $encrypter->decrypt($encrypter->encrypt($payload)));
    }

    /** @test */
    public function can_process_objects()
    {
        $payload = new \stdClass;
        $payload->foo = 'bar';
        $encrypter = new Encrypter('secret-key');

        $output = $encrypter->decrypt($encrypter->encrypt($payload));

        $this->assertInstanceOf(\stdClass::class, $output);
        $this->assertSame('bar', $output->foo);
    }
}
