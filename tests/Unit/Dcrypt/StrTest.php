<?php

namespace Rareloop\Lumberjack\Test\Unit\Dcrypt;

use Rareloop\Lumberjack\Dcrypt\Str;
use Rareloop\Lumberjack\Test\TestCase;

class StrTest extends TestCase
{
    public function testEquals()
    {
        // Test with hash_equals
        $this->assertTrue(Str::equal('2222', '2222'));
        $this->assertFalse(Str::equal('2222', '3333'));

        // Test without hash_equals
        $this->assertTrue(Str::equal('2222', '2222'));
        $this->assertFalse(Str::equal('2222', '3333'));
    }
}
