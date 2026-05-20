<?php

namespace Rareloop\Lumberjack\Test\Unit\Http\Resolvers;

use Brain\Monkey\Functions;
use PHPUnit\Framework\Attributes\Test;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Http\Resolvers\AbstractContextResolver;
use Rareloop\Lumberjack\Test\TestCase;
use Rareloop\Lumberjack\Test\Unit\Concerns\BrainMonkeyPHPUnitIntegration;
use Rareloop\Router\Invoker;
use WP_User;
use WP_Term;

class NullableMultiParameterTest extends TestCase
{
    use BrainMonkeyPHPUnitIntegration;

    private Application $app;
    private Invoker $invoker;

    protected function setUp(): void
    {
        parent::setUp();

        if (!class_exists(WP_User::class)) {
            eval('class WP_User {}');
        }

        if (!class_exists(WP_Term::class)) {
            eval('class WP_Term {}');
        }

        $this->app = new Application();
        $this->invoker = new Invoker($this->app);
    }

    #[Test]
    public function it_resolves_multiple_nullable_parameters_correctly()
    {
        // Simulate being on an Author page (WP_User context)
        Functions\expect('get_queried_object')->andReturn(new WP_User());

        // We'll use two real-world-like resolvers but as anonymous classes to keep it isolated
        $userResolver = new class extends AbstractContextResolver {
            protected function canResolveClass(string $className): bool
            {
                return $className === WP_User::class;
            }
            protected function isValidContext(mixed $context, string $className): bool
            {
                return is_a($context, WP_User::class);
            }
            protected function resolveObject(string $className, mixed $context): mixed
            {
                return $context;
            }
        };

            $termResolver = new class extends AbstractContextResolver {
                protected function canResolveClass(string $className): bool
                {
                    return $className === WP_Term::class;
                }
                protected function isValidContext(mixed $context, string $className): bool
                {
                    return is_a($context, WP_Term::class);
                }
                protected function resolveObject(string $className, mixed $context): mixed
                {
                    return $context;
                }
            };

        // Order matters in the chain, but with our fix it shouldn't prevent resolution
        $this->invoker->getParameterResolver()->prependResolver($userResolver);
        $this->invoker->getParameterResolver()->prependResolver($termResolver);

        $controller = new class {
            public function handle(?WP_Term $term, ?WP_User $user)
            {
                return ['term' => $term, 'user' => $user];
            }
        };

        // This would have previously thrown MismatchedContextException when TermResolver tried to handle WP_User
        $result = $this->invoker->call([$controller, 'handle']);

        $this->assertNull($result['term']);
        $this->assertInstanceOf(WP_User::class, $result['user']);
    }
}
