<?php

namespace Rareloop\Lumberjack\Test;

use Mockery;
use Mockery\Matcher\Closure;
use PHPUnit\Framework\TestCase;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Providers\ServiceProvider;
use Rareloop\Lumberjack\Test\Unit\BrainMonkeyPHPUnitIntegration;
use phpmock\Mock;
use phpmock\MockBuilder;
use Brain\Monkey;

class ApplicationTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    public function setUp()
    {
        Monkey\setUp();
        parent::setUp();
    }

    public function tearDown()
    {
        Monkey\tearDown();
        Mock::disableAll();
        parent::tearDown();
    }

    /** @test */
    public function base_path_is_set_in_container_when_basepath_passed_to_constructor()
    {
        $app = new Application('/base/path');

        $this->assertSame('/base/path', $app->basePath());
        $this->assertSame('/base/path', $app->get('path.base'));
    }

    /** @test */
    public function config_path_is_set_in_container_when_basepath_passed_to_constructor()
    {
        $app = new Application('/base/path');

        $this->assertSame('/base/path/config', $app->configPath());
        $this->assertSame('/base/path/config', $app->get('path.config'));
    }

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
    public function can_bind_an_object_and_always_get_the_same_instance_back()
    {
        $app = new Application;
        $object = new TestInterfaceImplementation;

        $app->bind(TestInterface::class, $object);

        $this->assertSame($app->get(TestInterface::class), $app->get(TestInterface::class));
    }

    /** @test */
    public function can_bind_a_concrete_class_to_an_interface()
    {
        $app = new Application;

        $app->bind(TestInterface::class, TestInterfaceImplementation::class);
        $object = $app->get(TestInterface::class);

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

        $object = $app->get(TestInterface::class);

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

        $object = $app->get(TestInterface::class);

        $this->assertSame(1, $count);
        $this->assertNotNull($object);
        $this->assertInstanceOf(TestInterfaceImplementation::class, $object);
    }

    /** @test */
    public function can_bind_a_singleton_concrete_class_to_an_interface()
    {
        $app = new Application;

        $app->singleton(TestInterface::class, TestInterfaceImplementation::class);

        $object1 = $app->get(TestInterface::class);
        $object2 = $app->get(TestInterface::class);

        $this->assertSame($object1, $object2);
        $this->assertNotNull($object1);
        $this->assertNotNull($object2);
        $this->assertInstanceOf(TestInterfaceImplementation::class, $object1);
        $this->assertInstanceOf(TestInterfaceImplementation::class, $object2);
    }

    /** @test */
    public function can_bind_a_singleton_concrete_class_with_constructor_params_to_an_interface()
    {
        $app = new Application;

        $app->singleton(TestInterface::class, TestInterfaceImplementationWithConstructorParams::class);

        $object1 = $app->get(TestInterface::class);
        $object2 = $app->get(TestInterface::class);

        $this->assertSame($object1, $object2);
        $this->assertNotNull($object1);
        $this->assertNotNull($object2);
        $this->assertInstanceOf(TestInterfaceImplementationWithConstructorParams::class, $object1);
        $this->assertInstanceOf(TestInterfaceImplementationWithConstructorParams::class, $object2);
    }

    /** @test */
    public function can_bind_a_singleton_with_closure()
    {
        $app = new Application;
        $count = 0;

        $app->singleton(TestInterface::class, function () use (&$count) {
            $count++;
            return new TestInterfaceImplementation();
        });

        $object1 = $app->get(TestInterface::class);
        $object2 = $app->get(TestInterface::class);

        $this->assertSame(1, $count);
        $this->assertSame($object1, $object2);
        $this->assertNotNull($object1);
        $this->assertNotNull($object2);
        $this->assertInstanceOf(TestInterfaceImplementation::class, $object1);
        $this->assertInstanceOf(TestInterfaceImplementation::class, $object2);
    }

    /** @test */
    public function can_bind_a_singleton_and_get_dependencies_injected()
    {
        $app = new Application;
        $count = 0;

        $app->bind(TestSubInterface::class, TestSubInterfaceImplementation::class);

        $app->singleton(TestInterface::class, function (TestSubInterface $foo) use (&$count) {
            $this->assertInstanceOf(TestSubInterfaceImplementation::class, $foo);
            $count++;
            return new TestInterfaceImplementation();
        });

        $object1 = $app->get(TestInterface::class);
        $object2 = $app->get(TestInterface::class);

        $this->assertSame(1, $count);
        $this->assertEquals($object1, $object2);
        $this->assertNotNull($object1);
        $this->assertNotNull($object2);
        $this->assertInstanceOf(TestInterfaceImplementation::class, $object1);
        $this->assertInstanceOf(TestInterfaceImplementation::class, $object2);
    }

    /** @test */
    public function app_should_be_bound_into_the_container_on_construction()
    {
        $app = new Application;

        $this->assertSame($app, $app->get(Application::class));
    }

    /** @test */
    public function can_create_a_class_that_has_not_been_registered()
    {
        $app = new Application;
        $app->bind(TestInterface::class, TestInterfaceImplementation::class);

        $object = $app->get(NotRegisteredInContainer::class);

        $this->assertInstanceOf(NotRegisteredInContainer::class, $object);
        $this->assertInstanceOf(TestInterfaceImplementation::class, $object->param);
    }

    /** @test */
    public function can_make_a_class_with_additional_params_for_the_constructor()
    {
        $app = new Application;
        $app->bind(TestInterface::class, TestInterfaceImplementation::class);

        $object = $app->make(RequiresAdditionalConstructorParams::class, [
            'param1' => 123,
            'param2' => 'abc',
        ]);

        $this->assertInstanceOf(RequiresAdditionalConstructorParams::class, $object);
        $this->assertInstanceOf(TestInterfaceImplementation::class, $object->param);
        $this->assertSame(123, $object->param1);
        $this->assertSame('abc', $object->param2);
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
    public function using_bind_does_not_produce_a_singleton()
    {
        $app = new Application;
        $app->bind(TestInterface::class, TestInterfaceImplementation::class);

        $object1 = $app->get(TestInterface::class);
        $object2 = $app->get(TestInterface::class);

        $this->assertNotSame($object1, $object2);
    }

    /** @test */
    public function get_does_not_produce_a_singleton_when_the_key_has_not_been_previously_bound_to_the_container()
    {
        $app = new Application;

        $object1 = $app->get(TestInterfaceImplementation::class);
        $object2 = $app->get(TestInterfaceImplementation::class);

        $this->assertNotSame($object1, $object2);
    }

    /** @test */
    public function using_bind_with_closure_does_not_produce_a_singleton()
    {
        $count = 0;
        $app = new Application;
        $app->bind(TestInterface::class, function () use (&$count) {
            $count++;
            return new TestInterfaceImplementation;
        });

        $object1 = $app->get(TestInterface::class);
        $object2 = $app->get(TestInterface::class);

        $this->assertNotSame($object1, $object2);
        $this->assertSame(2, $count);
    }

    /** @test */
    public function can_register_a_service_provider()
    {
        $app = new Application;
        $app->register(TestServiceProvider::class);

        $providers = $app->getLoadedProviders();

        $this->assertSame(1, count($providers));
        $this->assertInstanceOf(TestServiceProvider::class, $providers[0]);
    }

    /** @test */
    public function registered_service_provider_is_returned_by_register()
    {
        $app = new Application;

        $provider = $app->register(TestServiceProvider::class);

        $this->assertInstanceOf(TestServiceProvider::class, $provider);
    }

    /** @test */
    public function can_retrieve_a_registered_service_provider()
    {
        $app = new Application;

        $provider = $app->register(TestServiceProvider::class);

        $this->assertInstanceOf(TestServiceProvider::class, $app->getProvider(TestServiceProvider::class));
        $this->assertSame($provider, $app->getProvider(TestServiceProvider::class));
    }

    /** @test */
    public function can_retrieve_a_registered_service_provider_by_object()
    {
        $app = new Application;

        $provider = $app->register(TestServiceProvider::class);

        $this->assertInstanceOf(TestServiceProvider::class, $app->getProvider($provider));
        $this->assertSame($provider, $app->getProvider($provider));
    }

    /** @test */
    public function can_not_register_the_same_service_provider_twice()
    {
        $app = new Application;

        $provider1 = $app->register(TestServiceProvider::class);
        $provider2 = $app->register(TestServiceProvider::class);

        $providers = $app->getLoadedProviders();

        $this->assertSame(1, count($providers));
        $this->assertInstanceOf(TestServiceProvider::class, $providers[0]);
        $this->assertSame($provider1, $provider2);
    }

    /** @test */
    public function service_providers_without_register_functions_dont_cause_an_exception()
    {
        $app = new Application;
        $app->register(EmptyServiceProvider::class);

        $this->addToAssertionCount(1);  // does not throw an exception
    }

    /** @test */
    public function can_register_service_provider_from_an_object()
    {
        $app = new Application;
        $app->register(new TestServiceProvider($app));

        $providers = $app->getLoadedProviders();

        $this->assertSame(1, count($providers));
        $this->assertInstanceOf(TestServiceProvider::class, $providers[0]);
    }

    /** @test */
    public function registered_service_providers_have_their_register_function_called()
    {
        $app = new Application;
        $provider = Mockery::mock(TestServiceProvider::class, [$app]);
        $provider->shouldReceive('register')->once();

        $app->register($provider);
    }

    /** @test */
    public function calling_boot_on_app_should_call_boot_on_all_registered_service_providers()
    {
        $app = new Application;
        $provider = Mockery::mock(TestServiceProvider::class, [$app]);
        $provider->shouldReceive('register');
        $provider->shouldReceive('boot')->once();
        $app->register($provider);

        $app->boot();
    }

    /** @test */
    public function calling_boot_multiple_times_should_not_fire_boot_on_service_providers_more_than_once()
    {
        $app = new Application;
        $provider = Mockery::mock(TestServiceProvider::class, [$app]);
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
        $provider = new TestBootServiceProvider($app);
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
        $provider = Mockery::mock(TestServiceProvider::class, [$app]);
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

    /** @test */
    public function can_bootstrap_the_app_with_an_array_of_bootstrappers()
    {
        $app = new Application;
        $count = 0;
        $tester = new BootstrapperBootstrapTester(function () use (&$count) {
            $count++;
        });
        $app->bind(BootstrapperBootstrapTester::class, $tester);

        $app->bootstrapWith([TestBootstrapper1::class, TestBootstrapper2::class]);

        $this->assertSame(2, $count);
    }

    private function createPhpSapiNameMock($value, $namespace)
    {
        $builder = new MockBuilder();

        $builder->setNamespace($namespace)
                ->setName('php_sapi_name')
                ->setFunction(
                    function () use ($value) {
                        return $value;
                    }
                );

        return $builder->build();
    }

    /** @test */
    public function running_in_console_returns_true_for_cli()
    {
        $mock = $this->createPhpSapiNameMock('cli', 'Rareloop\Lumberjack');
        $mock->enable();
        $app = new Application;

        $this->assertTrue($app->runningInConsole());
    }

    /** @test */
    public function running_in_console_returns_true_for_phpdbg()
    {
        $mock = $this->createPhpSapiNameMock('phpdbg', 'Rareloop\Lumberjack');
        $mock->enable();
        $app = new Application;

        $this->assertTrue($app->runningInConsole());
    }

    /** @test */
    public function can_test_if_request_has_been_handled()
    {
        $app = new Application;

        $this->assertFalse($app->hasRequestBeenHandled());

        $app->requestHasBeenHandled();

        $this->assertTrue($app->hasRequestBeenHandled());
    }

    /** @test */
    public function calling_detectWhenRequestHasNotBeenHandled_adds_actions()
    {
        $app = new Application;

        $app->detectWhenRequestHasNotBeenHandled();

        $this->assertTrue(has_action('wp_footer'));
        $this->assertTrue(has_action('shutdown'));
    }
}

class BootstrapperBootstrapTester
{
    public function __construct($callback)
    {
        $this->callback = $callback;
    }
}

abstract class TestBootstrapperBase
{
    public function __construct(BootstrapperBootstrapTester $tester)
    {
        $this->tester = $tester;
    }

    public function bootstrap(Application $app)
    {
        call_user_func($this->tester->callback);
    }
}

class TestBootstrapper1 extends TestBootstrapperBase
{

}

class TestBootstrapper2 extends TestBootstrapperBase
{

}

interface TestInterface
{

}

class TestInterfaceImplementation implements TestInterface
{

}

class TestInterfaceImplementationWithConstructorParams implements TestInterface
{
    public function __construct(TestServiceProvider $provider) {}
}

interface TestSubInterface
{

}

class TestSubInterfaceImplementation implements TestSubInterface
{

}

class TestServiceProvider extends ServiceProvider
{
    public function register() {}
    public function boot() {}
}

class EmptyServiceProvider extends ServiceProvider
{

}

class TestBootServiceProvider extends ServiceProvider
{
    private $bootCallback;

    public function register() {}

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

class RequiresAdditionalConstructorParams
{
    public $param;
    public $param1;
    public $param2;

    public function __construct(TestInterface $test, $param1, $param2)
    {
        $this->param = $test;
        $this->param1 = $param1;
        $this->param2 = $param2;
    }
}
