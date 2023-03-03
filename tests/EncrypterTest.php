<?php

namespace Rareloop\Lumberjack\Test;

use Dcrypt\AesCbc;
use Mockery;
use PHPUnit\Framework\TestCase;
use Rareloop\Lumberjack\Config;
use Rareloop\Lumberjack\Encrypter;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class EncrypterTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    /** @test */
    public function can_encrypt_data()
    {
        $key = 'secret-key';
        $dcrypt = Mockery::mock('alias:' . AesCbc::class);
        $dcrypt->shouldReceive('encrypt')->withArgs(function ($data, $key) {
            if ($key !== 'secret-key') {
                return false;
            }

            if ($data !== @serialize('test-string')) {
                return false;
            }

            return true;
        })->once();

        $encrypter = new Encrypter($key);
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
