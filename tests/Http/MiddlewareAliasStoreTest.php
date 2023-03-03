<?php

namespace Rareloop\Lumberjack\Test\Http;

use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\MiddlewareInterface;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Http\MiddlewareAliasStore;

class MiddlewareAliasStoreTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    /** @test */
    public function can_register_an_alias_for_a_middleware_object()
    {
        $store = new MiddlewareAliasStore;
        $middleware = Mockery::mock(MiddlewareInterface::class);

        $store->set('middlewarekey', $middleware);

        $this->assertSame($middleware, $store->get('middlewarekey'));
    }

    /** @test */
    public function can_register_an_alias_for_a_middleware_closure_factory()
    {
        $store = new MiddlewareAliasStore;
        $middleware = Mockery::mock(MiddlewareInterface::class);

        $store->set('middlewarekey', function () use ($middleware) {
            return $middleware;
        });

        $this->assertSame($middleware, $store->get('middlewarekey'));
    }

    /** @test */
    public function can_register_an_alias_for_a_classname()
    {
        $store = new MiddlewareAliasStore();

        $store->set('middlewarekey', MASTestClass::class);

        $this->assertInstanceOf(MASTestClass::class, $store->get('middlewarekey'));
    }

    /** @test */
    public function can_register_an_alias_with_params_for_a_middleware_closure_factory()
    {
        $store = new MiddlewareAliasStore;
        $middleware = Mockery::mock(MiddlewareInterface::class);

        $store->set('middlewarekey', function ($param1, $param2) use ($middleware) {
            $this->assertSame('123', $param1);
            $this->assertSame('abc', $param2);
            return $middleware;
        });

        $this->assertSame($middleware, $store->get('middlewarekey:123,abc'));
    }

    /** @test */
    public function can_register_an_alias_with_params_for_a_classname()
    {
        $store = new MiddlewareAliasStore;

        $store->set('middlewarekey', MASTestClassWithConstructorParams::class);
        $middleware = $store->get('middlewarekey:123,abc');

        $this->assertInstanceOf(MASTestClassWithConstructorParams::class, $middleware);
        $this->assertSame('123', $middleware->param1);
        $this->assertSame('abc', $middleware->param2);
    }

    /** @test */
    public function can_check_if_alias_exists()
    {
        $store = new MiddlewareAliasStore;
        $middleware = Mockery::mock(MiddlewareInterface::class);

        $this->assertFalse($store->has('middlewarekey'));

        $store->set('middlewarekey', $middleware);

        $this->assertTrue($store->has('middlewarekey'));
    }

    /** @test */
    public function can_check_if_alias_exists_when_string_contains_params()
    {
        $store = new MiddlewareAliasStore;
        $middleware = Mockery::mock(MiddlewareInterface::class);

        $this->assertFalse($store->has('middlewarekey'));

        $store->set('middlewarekey', $middleware);

        $this->assertTrue($store->has('middlewarekey:param1,param2'));
    }
}

class MASTestClass
{

}

class MASTestClassWithConstructorParams
{
    public $param1;
    public $param2;

    public function __construct($param1, $param2)
    {
        $this->param1 = $param1;
        $this->param2 = $param2;
    }
}
