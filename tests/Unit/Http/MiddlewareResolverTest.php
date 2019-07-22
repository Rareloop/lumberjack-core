<?php

namespace Rareloop\Lumberjack\Test\Http;

use Mockery;
use PHPUnit\Framework\TestCase;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Contracts\MiddlewareAliases;
use Rareloop\Lumberjack\Http\MiddlewareAliasStore;
use Rareloop\Lumberjack\Http\MiddlewareResolver;

class MiddlewareResolverTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    /** @test */
    public function can_resolve_a_key_from_the_container()
    {
        $app = new Application;
        $resolver = new MiddlewareResolver($app, new MiddlewareAliasStore);

        $datetime = new \DateTime;
        $app->bind('datetime', $datetime);

        $this->assertSame($datetime, $resolver->resolve('datetime'));
    }

    /** @test */
    public function can_resolve_an_object_from_a_classname_from_the_container()
    {
        $app = new Application;
        $resolver = new MiddlewareResolver($app, new MiddlewareAliasStore);

        $this->assertInstanceOf(MRTestClass::class, $resolver->resolve(MRTestClass::class));
    }

    /** @test */
    public function can_resolve_a_middleware_alias()
    {
        $app = new Application;
        $store = Mockery::mock(MiddlewareAliases::class);
        $store->shouldReceive('has')->with('middlewarekey')->once()->andReturn(true);
        $store->shouldReceive('get')->with('middlewarekey')->once()->andReturn(new MRTestClass);
        $resolver = new MiddlewareResolver($app, $store);

        $this->assertInstanceOf(MRTestClass::class, $resolver->resolve('middlewarekey'));
    }

    /** @test */
    public function non_string_values_are_returned_as_is()
    {
        $app = new Application;
        $resolver = new MiddlewareResolver($app, new MiddlewareAliasStore);

        $datetime = new \DateTime;

        $this->assertSame($datetime, $resolver->resolve($datetime));
    }
}

class MRTestClass
{

}
