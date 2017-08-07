<?php

namespace Rareloop\Lumberjack\Test;

use PHPUnit\Framework\TestCase;
use Rareloop\Lumberjack\Application;
use Mockery;

class ApplicationTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

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
    public function app_should_be_bound_into_the_container_on_construction()
    {
        $app = new Application;

        $this->assertSame($app, $app->get(Application::class));
    }

    /** @test */
    public function can_make_a_class_that_has_not_been_registered()
    {
        $app = new Application;
        $app->bind(TestInterface::class, TestInterfaceImplementation::class);

        $object = $app->make(NotRegisteredInContainer::class);

        $this->assertInstanceOf(NotRegisteredInContainer::class, $object);
        $this->assertInstanceOf(TestInterfaceImplementation::class, $object->param);
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

    /** @test */
    public function can_register_a_service_provider()
    {
        $app = new Application;
        $provider = new TestServiceProvider;
        $app->register($provider);

        $providers = $app->getLoadedProviders();

        $this->assertContains($provider, $providers);
    }

    /** @test */
    public function service_providers_without_register_functions_dont_cause_an_exception()
    {
        $app = new Application;
        $provider = new EmptyServiceProvider;
        $app->register($provider);

        $this->addToAssertionCount(1);  // does not throw an exception
    }

    /** @test */
    public function registered_service_providers_have_their_register_function_called()
    {
        $app = new Application;
        $provider = Mockery::mock(TestServiceProvider::class);
        $provider->shouldReceive('register')->once()->with($app);

        $app->register($provider);
    }

    /** @test */
    public function calling_boot_on_app_should_call_boot_on_all_registered_service_providers()
    {
        $app = new Application;
        $provider = Mockery::mock(new TestServiceProvider);
        $provider->shouldReceive('register');
        $provider->shouldReceive('boot')->once();
        $app->register($provider);

        $app->boot();
    }

    /** @test */
    public function calling_boot_multiple_times_should_not_fire_boot_on_service_providers_more_than_once()
    {
        $app = new Application;
        $provider = Mockery::mock(new TestServiceProvider);
        $provider->shouldReceive('register');
        $provider->shouldReceive('boot')->once();
        $app->register($provider);

        $app->boot();
        $app->boot();
    }

    /** @test */
    public function boot_should_resolve_dependencies_from_container_on_service_providers()
    {
        $app = new Application;
        $app->bind(TestInterface::class, TestInterfaceImplementation::class);
        $provider = new TestBootServiceProvider;
        $count = 0;

        $provider->addBootCallback(function (array $args) use (&$count, $app) {
            $count++;
            $this->assertInstanceOf(Application::class, $args[0]);
            $this->assertSame($app, $args[0]);
            $this->assertInstanceOf(TestInterfaceImplementation::class, $args[1]);
        });

        $app->register($provider);

        $app->boot();

        $this->assertSame(1, $count);
    }

    /** @test */
    public function services_registered_after_boot_should_have_their_boot_method_called_straight_away()
    {
        $app = new Application;
        $provider = Mockery::mock(new TestServiceProvider);
        $provider->shouldReceive('register');
        $provider->shouldReceive('boot')->once();

        $app->boot();
        $app->register($provider);
    }

    /** @test */
    public function is_booted_returns_false_before_boot_method_has_been_called()
    {
        $app = new Application;

        $this->assertFalse($app->isBooted());
    }

    /** @test */
    public function is_booted_returns_true_after_boot_method_has_been_called()
    {
        $app = new Application;

        $app->boot();

        $this->assertTrue($app->isBooted());
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

class TestServiceProvider
{
    public function register(Application $app) {}
    public function boot() {}
}

class EmptyServiceProvider
{

}

class TestBootServiceProvider
{
    private $bootCallback;

    public function register(Application $app) {}

    public function boot(Application $app, TestInterface $test) {
        if (isset($this->bootCallback)) {
            call_user_func($this->bootCallback, func_get_args());
        }
    }

    public function addBootCallback(\Closure $callback) {
        $this->bootCallback = $callback;
    }
}

class NotRegisteredInContainer
{
    public $param;

    public function __construct(TestInterface $test)
    {
        $this->param = $test;
    }
}
