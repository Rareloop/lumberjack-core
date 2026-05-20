<?php

namespace Rareloop\Lumberjack\Test\Unit\Http\Resolvers;

use stdClass;
use Exception;
use Brain\Monkey\Functions;
use PHPUnit\Framework\Attributes\Test;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Http\Resolvers\AbstractContextResolver;
use Rareloop\Lumberjack\Test\TestCase;
use Rareloop\Lumberjack\Test\Unit\Concerns\BrainMonkeyPHPUnitIntegration;
use Rareloop\Router\Invoker;
use Rareloop\Lumberjack\Exceptions\MissingContextException;
use Rareloop\Lumberjack\Exceptions\MismatchedContextException;

class AbstractContextResolverTest extends TestCase
{
    use BrainMonkeyPHPUnitIntegration;

    private Application $app;
    private TestContextResolver $resolver;
    private Invoker $invoker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app = new Application();
        $this->resolver = new TestContextResolver();

        // We use a fresh Invoker with ONLY our test resolver to verify the base class logic
        $this->invoker = new Invoker($this->app);
        $this->invoker->getParameterResolver()->prependResolver($this->resolver);
    }

    #[Test]
    public function it_ignores_builtin_typehints(): void
    {
        $controller = new class {
            public function handle(int $id, string $name, $noType)
            {
                return $id;
            }
        };

        $result = $this->invoker->call([$controller, 'handle'], ['id' => 123, 'name' => 'foo', 'noType' => 'bar']);

        $this->assertSame(123, $result);
    }

    #[Test]
    public function it_ignores_classes_it_cannot_handle(): void
    {
        $this->resolver->canResolve = false;

        $controller = new class {
            public function handle(Application $app)
            {
                return $app;
            }
        };

        $result = $this->invoker->call([$controller, 'handle']);

        $this->assertSame($this->app, $result);
    }

    #[Test]
    public function it_throws_an_exception_if_context_is_missing_and_not_nullable(): void
    {
        Functions\expect('get_queried_object')->once()->andReturn(null);

        $controller = new class {
            public function handle(stdClass $obj)
            {
            }
        };

        $this->expectException(MissingContextException::class);
        $this->invoker->call([$controller, 'handle']);
    }

    #[Test]
    public function it_resolves_to_null_if_context_is_missing_and_nullable(): void
    {
        Functions\expect('get_queried_object')->once()->andReturn(null);

        $controller = new class {
            public function handle(?stdClass $obj)
            {
                return $obj;
            }
        };

        $result = $this->invoker->call([$controller, 'handle']);

        $this->assertNull($result);
    }

    #[Test]
    public function it_throws_an_exception_if_resolved_object_is_wrong_type(): void
    {
        Functions\expect('get_queried_object')->once()->andReturn(new stdClass());

        $this->resolver->resolvedObject = new Exception(); // Not a stdClass

        $controller = new class {
            public function handle(stdClass $obj)
            {
            }
        };

        $this->expectException(MismatchedContextException::class);
        $this->invoker->call([$controller, 'handle']);
    }
}

class TestContextResolver extends AbstractContextResolver
{
    public bool $canResolve = true;
    public bool $isValid = true;
    public $resolvedObject;

    protected function canResolveClass(string $className): bool
    {
        return $this->canResolve;
    }

    protected function isValidContext(mixed $context, string $className): bool
    {
        return $this->isValid;
    }

    protected function resolveObject(string $className, mixed $context): mixed
    {
        return $this->resolvedObject ?? new stdClass();
    }
}
