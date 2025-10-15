<?php

namespace Rareloop\Lumberjack\Test\Unit\Facades\Stubs;

class Foo implements FooInterface
{
    public function foo(): string
    {
        return 'bar';
    }
}
