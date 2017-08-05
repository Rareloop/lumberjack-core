<?php

namespace Rareloop\Lumberjack\Test;

use PHPUnit\Framework\TestCase;
use Rareloop\Lumberjack\Application;

class ApplicationTest extends TestCase
{
    /** @test */
    public function can_bind_a_value()
    {
        $app = new Application;

        $app->bind('app.environment', 'production');

        $this->assertSame('production', $app->get('app.environment'));
    }

    /** @test */
    public function can_determine_if_something_has_been_bound()
    {
        $app = new Application;

        $this->assertFalse($app->has('app.environment'));
        $app->bind('app.environment', 'production');
        $this->assertTrue($app->has('app.environment'));
    }

    /** @test */
    public function can_bind_an_object()
    {
        $app = new Application;
        $object = new TestInterfaceImplementation;

        $app->bind('test', $object);

        $this->assertSame($object, $app->get('test'));
    }

    /** @test */
    public function can_bind_a_concrete_class_to_an_interface()
    {
        $app = new Application;

        $app->bind(TestInterface::class, TestInterfaceImplementation::class);
        $object = $app->make(TestInterface::class);

        $this->assertNotNull($object);
        $this->assertInstanceOf(TestInterfaceImplementation::class, $object);
    }

    /** @test */
    public function can_bind_using_closure()
    {
        $app = new Application;
        $count = 0;

        $app->bind(TestInterface::class, function () use (&$count) {
            $count++;
            return new TestInterfaceImplementation();
        });

        $object = $app->make(TestInterface::class);

        $this->assertSame(1, $count);
        $this->assertNotNull($object);
        $this->assertInstanceOf(TestInterfaceImplementation::class, $object);
    }

    /** @test */
    public function can_bind_using_closure_and_get_dependencies_injected()
    {
        $app = new Application;
        $count = 0;

        $app->bind(TestSubInterface::class, TestSubInterfaceImplementation::class);
        $app->bind(TestInterface::class, function (TestSubInterface $foo) use (&$count) {
            $this->assertInstanceOf(TestSubInterfaceImplementation::class, $foo);
            $count++;
            return new TestInterfaceImplementation();
        });

        $object = $app->make(TestInterface::class);

        $this->assertSame(1, $count);
        $this->assertNotNull($object);
        $this->assertInstanceOf(TestInterfaceImplementation::class, $object);
    }

    /** @test */
    public function make_produces_unique_instances_of_the_bound_object()
    {
        $app = new Application;
        $app->bind(TestInterface::class, TestInterfaceImplementation::class);

        $object1 = $app->make(TestInterface::class);
        $object2 = $app->make(TestInterface::class);

        $this->assertNotSame($object1, $object2);
    }
}

interface TestInterface
{

}

class TestInterfaceImplementation implements TestInterface
{

}

interface TestSubInterface
{

}

class TestSubInterfaceImplementation implements TestSubInterface
{

}
