<?php

namespace Rareloop\Lumberjack\Test\Facades;

use DI\Container;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Rareloop\Lumberjack\FacadeManager;
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

    /** @test */
    public function can_initiate_facades()
    {
        FacadeManager::setContainer($this->container);
        $this->assertInstanceOf(ContainerInterface::class, FacadeManager::getContainer());
    }

    /** @test */
    public function can_get_facade_instance()
    {
        FacadeManager::setContainer($this->container);

        $instance = FooFacade::__instance();

        $this->assertInstanceOf(FooInterface::class, $instance);
        $this->assertInstanceOf(Foo::class, $instance);
    }

    /** @test */
    public function can_swap_instances()
    {
        FacadeManager::setContainer($this->container);

        $instance = FooFacade::__instance();

        $this->assertInstanceOf(FooInterface::class, $instance);
        $this->assertInstanceOf(Foo::class, $instance);

        $this->container->set(FooFacade::accessor(), 'bar');

        $instance = FooFacade::__instance();

        $this->assertEquals('bar', $instance);
    }

    public function can_call_functions()
    {
        FacadeManager::setContainer($this->container);

        $this->assertEquals('bar', forward_static_call([FooFacade::class, 'foo']));
        $this->assertEquals('bar', call_user_func('FooFacade::class::foo'));
        $this->assertEquals('bar', FooFacade::foo());
    }
}
