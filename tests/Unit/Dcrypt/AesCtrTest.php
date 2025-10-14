<?php

namespace Rareloop\Lumberjack\Test\Unit\Dcrypt;

use Rareloop\Lumberjack\Dcrypt\AesCtr;

class AesCtrTest extends TestSupport
{
    private $input = 'AAAAAAAA', $key = 'BBBBBBBB';

    public function testPbkdf()
    {
        $encrypted = AesCtr::encrypt($this->input, $this->key, 10);
        $this->assertEquals($this->input, AesCtr::decrypt($encrypted, $this->key, 10));
    }

    public function testEngine()
    {
        $encrypted = AesCtr::encrypt($this->input, $this->key);
        $this->assertEquals($this->input, AesCtr::decrypt($encrypted, $this->key));
    }

    public function testCorrupt()
    {
        $this->expectException(\InvalidArgumentException::class);

        $encrypted = AesCtr::encrypt($this->input, $this->key);

        // Perform a validation by replacing a random byte to make sure
        // the decryption fails. After enough successful runs,
        // all areas of the cypher text will have been tested
        // for integrity
        $corrupt = self::swaprandbyte($encrypted);
        AesCtr::decrypt($corrupt, $this->key);
    }

    public function testVector()
    {
        $input = 'hello world';
        $pass = 'password';
        $vector = \base64_decode('Vpbd71CIVcRPALeSg126DhRKYozXlbusn/eSSxrQPtzj/U7hOhlN8D21Y0gmlmUKorpoXuDS6bklvD8=');
        $this->assertEquals($input, AesCtr::decrypt($vector, $pass));
    }
}
