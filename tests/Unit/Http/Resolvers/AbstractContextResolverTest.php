<?php

namespace Rareloop\Lumberjack\Test\Unit\Http\Resolvers;

use Brain\Monkey\Functions;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Rareloop\Lumberjack\Http\Resolvers\AbstractContextResolver;
use Rareloop\Lumberjack\Test\TestCase;
use Rareloop\Lumberjack\Test\Unit\Concerns\BrainMonkeyPHPUnitIntegration;
use ReflectionFunction;

class AbstractContextResolverTest extends TestCase
{
    use BrainMonkeyPHPUnitIntegration;

    #[Test]
    public function it_ignores_builtin_typehints(): void
    {
        $resolver = new class extends AbstractContextResolver {
            protected function canResolveClass(string $className): bool
            {
                return true;
            }
            protected function resolveObject(string $className, mixed $context): mixed
            {
                return new \stdClass();
            }
        };

        $reflection = new ReflectionFunction(function (int $id, string $name, $noType) {
        });

        $this->assertEmpty($resolver->getParameters($reflection, [], []));
    }

    #[Test]
    public function it_ignores_classes_it_cannot_handle(): void
    {
        $resolver = new class extends AbstractContextResolver {
            protected function canResolveClass(string $className): bool
            {
                return $className === \stdClass::class;
            }
            protected function resolveObject(string $className, mixed $context): mixed
            {
                return new \stdClass();
            }
        };

        $reflection = new ReflectionFunction(function (\Exception $e) {
        });

        $this->assertEmpty($resolver->getParameters($reflection, [], []));
    }

    #[Test]
    public function it_throws_an_exception_if_context_is_missing_and_not_nullable(): void
    {
        Functions\expect('get_queried_object')->once()->andReturn(null);

        $resolver = new class extends AbstractContextResolver {
            protected function canResolveClass(string $className): bool
            {
                return true;
            }
            protected function resolveObject(string $className, mixed $context): mixed
            {
                return new \stdClass();
            }
        };

        $reflection = new ReflectionFunction(function (\stdClass $obj) {
        });

        $this->expectException(\Rareloop\Lumberjack\Exceptions\MissingContextException::class);
        $resolver->getParameters($reflection, [], []);
    }

    #[Test]
    public function it_resolves_to_null_if_context_is_missing_and_nullable(): void
    {
        Functions\expect('get_queried_object')->once()->andReturn(null);

        $resolver = new class extends AbstractContextResolver {
            protected function canResolveClass(string $className): bool
            {
                return true;
            }
            protected function resolveObject(string $className, mixed $context): mixed
            {
                return new \stdClass();
            }
        };

        $reflection = new ReflectionFunction(function (?\stdClass $obj) {
        });
        $resolved = $resolver->getParameters($reflection, [], []);

        $this->assertCount(1, $resolved);
        $this->assertNull($resolved[0]);
    }

    #[Test]
    public function it_throws_an_exception_if_resolved_object_is_wrong_type(): void
    {
        Functions\expect('get_queried_object')->once()->andReturn(new \stdClass());

        $resolver = new class extends AbstractContextResolver {
            protected function canResolveClass(string $className): bool
            {
                return true;
            }
            protected function resolveObject(string $className, mixed $context): mixed
            {
                return new \Exception(); // Not a stdClass
            }
        };

        $reflection = new ReflectionFunction(function (\stdClass $obj) {
        });

        $this->expectException(\Rareloop\Lumberjack\Exceptions\MismatchedContextException::class);
        $resolver->getParameters($reflection, [], []);
    }
}
