<?php

namespace Rareloop\Lumberjack\Test;

use Mockery;
use ReflectionProperty;
use PHPUnit\Framework\TestCase;
use Rareloop\Lumberjack\Encrypter;
use Illuminate\Encryption\Encrypter as IlluminateEncrypter;
use Illuminate\Contracts\Encryption\EncryptException as IlluminateEncryptException;

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

        $ref = new ReflectionProperty($encrypter, 'encrypter');
        $ref->setAccessible(true);
        $ref->setValue($encrypter, $illuminateMock);

        $encrypter->encrypt('test-string');
    }

    /** @test */
    public function can_decrypt_data_using_old_key()
    {
        $key = 'YurEU4attIXDGJWTL5VNvEXkMTosdBah';
        // $string = 'ixTgrcIr6+GvOB/S0Fdt4e4zbeLthAfqE/IINyhLI8zvLEgna/7yS7/uP1p7fZVdDDXuOlXutgkBJ4JnzFMnUD844YNa5b6k6aSk/v3+n4g=';
        $string = 'eyJpdiI6InVjVHNkSWtlbnRESHZOVzlWMG9JcUE9PSIsInZhbHVlIjoiUDV4d3BlSy9jMTlpRTFNUGtHM0hmd3h6SHNHYTg1TzAyWmJHaWNSWi9iST0iLCJtYWMiOiJkNDgyNTc3YTk3ZTMwMjNmYjFkOWRiZDg3MTI5NDliMzhiMmU3N2ZmZWRhMGRhNGI5ZTc0MWMxYjdlODA5MmZmIiwidGFnIjoiIn0=';

        $encrypter = new Encrypter($key);

        $this->assertSame('test-string', $encrypter->decrypt($string));
    }

    /** @test */
    public function can_decrypt_data_that_was_encrypted_with_dcrypt_package()
    {
        $key = 'YurEU4attIXDGJWTL5VNvEXkMTosdBah';
        $string = '3yHCC7zeDOxdAfJLy8np/yynZ/0YoLFoE9xdDrHaJD8to7+QyyMtyXjiZ16UXXxwxEnQc7jzZJ9w3dAIokA03WpahumAZO2JFHgTQTL/DKc=';

        $encrypter = new Encrypter($key);
        $encrypter->encrypt('test-string');

        $this->markTestIncomplete('Data is serialised differently between packages');
        $this->assertSame('test-string', $encrypter->decrypt($string));
    }

    /** @test */
    public function can_decrypt_data_using_base64_key()
    {
        $key = 'base64:ydjOTEkQq2WsMwzhSgq4xuv392AdyjENcO8/VrNl37w=';
        $string = 'eyJpdiI6IklGL2JDOUVoK1ZucU5Na1lFdjRjOXc9PSIsInZhbHVlIjoiVDA3OGtPZUE2a2wrdUE3K2t1c2RoSkhaREJmRzVUY3NGaS8xdmpuR2tIND0iLCJtYWMiOiI0N2Y2NjJmYzZiMWFiN2I0ZGNiZjFmNWJiNzIzOWQ5ZGI5M2FjM2VhZDRmNDZiMDk5M2YwNDFhMWU2OGEwMjVhIiwidGFnIjoiIn0=';

        $encrypter = new Encrypter($key);

        $this->assertSame('test-string', $encrypter->decrypt($string));
    }

    /** @test */
    public function can_process_strings()
    {
        $payload = 'test-string';
        $encrypter = new Encrypter('base64:ydjOTEkQq2WsMwzhSgq4xuv392AdyjENcO8/VrNl37w=');

        $this->assertSame($payload, $encrypter->decrypt($encrypter->encrypt($payload)));
    }

    /** @test */
    public function can_process_arrays()
    {
        $payload = ['foo' => 'bar'];
        $encrypter = new Encrypter('base64:ydjOTEkQq2WsMwzhSgq4xuv392AdyjENcO8/VrNl37w=');

        $this->assertSame($payload, $encrypter->decrypt($encrypter->encrypt($payload)));
    }

    /** @test */
    public function can_process_objects()
    {
        $payload = new \stdClass;
        $payload->foo = 'bar';
        $encrypter = new Encrypter('base64:ydjOTEkQq2WsMwzhSgq4xuv392AdyjENcO8/VrNl37w=');

        $output = $encrypter->decrypt($encrypter->encrypt($payload));

        $this->assertInstanceOf(\stdClass::class, $output);
        $this->assertSame('bar', $output->foo);
    }

    /** @test */
    public function cannot_use_invalid_key()
    {
        $this->expectException(\Rareloop\Lumberjack\Exceptions\InvalidEncryptionKeyException::class);

        new Encrypter('invalid-key');
    }

    /** @test */
    public function cannot_decrypt_invalid_string()
    {
        $this->expectException(\Rareloop\Lumberjack\Exceptions\DecryptException::class);

        $encrypter = new Encrypter('base64:ydjOTEkQq2WsMwzhSgq4xuv392AdyjENcO8/VrNl37w=');
        $encrypter->decrypt('foo');
    }

    /** @test */
    public function cannot_encrypt_thing()
    {
        $this->expectException(\Rareloop\Lumberjack\Exceptions\EncryptException::class);

        $illuminateMock = Mockery::mock(IlluminateEncrypter::class);
        $illuminateMock
            ->shouldReceive('encrypt')
            ->with('test-string')
            ->once()
            ->andThrow(new IlluminateEncryptException('Could not encrypt the data.'));

        $encrypter = new Encrypter('base64:ydjOTEkQq2WsMwzhSgq4xuv392AdyjENcO8/VrNl37w=');

        $ref = new ReflectionProperty($encrypter, 'encrypter');
        $ref->setAccessible(true);
        $ref->setValue($encrypter, $illuminateMock);

        $encrypter->encrypt('test-string');
    }
}
