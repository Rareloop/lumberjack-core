<?php

namespace Rareloop\Lumberjack\Test\Facades;

use DI\Container;
use PHPUnit\Framework\Attributes\Test;
use Rareloop\Lumberjack\Test\TestCase;
use Psr\Container\ContainerInterface;
use Rareloop\Lumberjack\FacadeFactory;
use Rareloop\Lumberjack\Facades\Facade;
use Rareloop\Lumberjack\Test\Unit\Facades\Stubs\Foo;
use Rareloop\Lumberjack\Test\Unit\Facades\Stubs\FooFacade;
use Rareloop\Lumberjack\Test\Unit\Facades\Stubs\FooInterface;

class FacadeTest extends TestCase
{
    private ?Container $container;

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = new Container();
        $this->container->set(FooFacade::accessor(), new Foo());
    }

    #[Test]
    public function can_initiate_facades()
    {
        FacadeFactory::setContainer($this->container);
        $this->assertInstanceOf(ContainerInterface::class, FacadeFactory::getContainer());
    }

    #[Test]
    public function can_get_facade_instance()
    {
        FacadeFactory::setContainer($this->container);

        $instance = FooFacade::__instance();

        $this->assertInstanceOf(FooInterface::class, $instance);
        $this->assertInstanceOf(Foo::class, $instance);
    }

    #[Test]
    public function can_swap_instances()
    {
        FacadeFactory::setContainer($this->container);

        $instance = FooFacade::__instance();

        $this->assertInstanceOf(FooInterface::class, $instance);
        $this->assertInstanceOf(Foo::class, $instance);

        $this->container->set(FooFacade::accessor(), 'bar');

        $instance = FooFacade::__instance();

        $this->assertEquals('bar', $instance);
    }

    #[Test]
    public function can_call_functions()
    {
        FacadeFactory::setContainer($this->container);

        $this->assertEquals('bar', forward_static_call([FooFacade::class, 'foo']));
        $this->assertEquals('bar', call_user_func([FooFacade::class, 'foo']));
        $this->assertEquals('bar', FooFacade::foo());
    }
}
