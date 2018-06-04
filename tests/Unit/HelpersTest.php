<?php

namespace Rareloop\Lumberjack\Test;

use PHPUnit\Framework\TestCase;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Helpers;

class HelpersTest extends TestCase
{
    /** @test */
    public function can_retrieve_the_container_instance()
    {
        $app = new Application;

        $this->assertSame($app, Helpers::app());
    }

    /** @test */
    public function can_resolve_something_from_the_container()
    {
        $app = new Application;
        $app->bind('test', 123);

        $this->assertSame(123, Helpers::app('test'));
    }

    /** @test */
    public function can_make_a_class_with_additional_params_for_the_constructor()
    {
        $app = new Application;

        $object = Helpers::app(RequiresConstructorParams::class, [
            'param1' => 123,
            'param2' => 'abc',
        ]);

        $this->assertInstanceOf(RequiresConstructorParams::class, $object);
        $this->assertSame(123, $object->param1);
        $this->assertSame('abc', $object->param2);
    }
}

class RequiresConstructorParams
{
    public $param1;
    public $param2;

    public function __construct($param1, $param2)
    {
        $this->param1 = $param1;
        $this->param2 = $param2;
    }
}
