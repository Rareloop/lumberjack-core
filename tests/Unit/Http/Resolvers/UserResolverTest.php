<?php

namespace Rareloop\Lumberjack\Test\Unit\Http\Resolvers;

use Brain\Monkey\Functions;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Providers\WordPressControllersServiceProvider;
use Rareloop\Lumberjack\Test\TestCase;
use Rareloop\Lumberjack\Test\Unit\Concerns\BrainMonkeyPHPUnitIntegration;
use Rareloop\Lumberjack\Test\Unit\Http\Resolvers\Stubs\UserStub;
use Rareloop\Router\Invoker;
use Timber\User;
use Timber\Timber;
use WP_User;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class UserResolverTest extends TestCase
{
    use BrainMonkeyPHPUnitIntegration;

    private Application $app;
    private Invoker $invoker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app = new Application(__DIR__ . '/../../../../');

        $config = Mockery::mock(\Rareloop\Lumberjack\Config::class);
        $config->shouldReceive('get')->with('app.resolvers', [])->andReturn([]);
        $this->app->bind('config', $config);

        $provider = new WordPressControllersServiceProvider($this->app);
        $provider->register();

        $this->invoker = $this->app->make(Invoker::class);
    }

    #[Test]
    public function it_can_resolve_a_timber_user(): void
    {
        $wpUser = Mockery::mock(WP_User::class);
        Functions\expect('get_queried_object')->once()->andReturn($wpUser);

        $timberUser = Mockery::mock(User::class);
        $timber = Mockery::mock('alias:' . Timber::class);
        $timber->shouldReceive('get_user')->once()->with($wpUser)->andReturn($timberUser);

        $controller = new class {
            public function handle(User $user)
            {
                return $user;
            }
        };

        $result = $this->invoker->call([$controller, 'handle']);

        $this->assertSame($timberUser, $result);
    }

    #[Test]
    public function it_can_resolve_a_subclass_of_timber_user(): void
    {
        $wpUser = Mockery::mock(WP_User::class);
        Functions\expect('get_queried_object')->once()->andReturn($wpUser);

        $userStub = Mockery::mock(UserStub::class);
        $timber = Mockery::mock('alias:' . Timber::class);
        $timber->shouldReceive('get_user')->once()->with($wpUser)->andReturn($userStub);

        $controller = new class {
            public function handle(UserStub $user)
            {
                return $user;
            }
        };

        $result = $this->invoker->call([$controller, 'handle']);

        $this->assertSame($userStub, $result);
    }

    #[Test]
    public function it_throws_an_exception_if_the_queried_object_is_not_a_user(): void
    {
        Functions\expect('get_queried_object')->once()->andReturn(null);

        $controller = new class {
            public function handle(User $user)
            {
            }
        };

        $this->expectException(\Rareloop\Lumberjack\Exceptions\MissingContextException::class);

        $this->invoker->call([$controller, 'handle']);
    }

    #[Test]
    public function it_resolves_to_null_if_the_typehint_is_nullable_and_context_is_missing(): void
    {
        Functions\expect('get_queried_object')->once()->andReturn(null);

        $controller = new class {
            public function handle(?User $user)
            {
                return $user;
            }
        };

        $result = $this->invoker->call([$controller, 'handle']);

        $this->assertNull($result);
    }

    #[Test]
    public function it_throws_an_exception_if_the_resolved_user_is_not_of_the_expected_typehinted_class(): void
    {
        $wpUser = Mockery::mock(WP_User::class);
        Functions\expect('get_queried_object')->once()->andReturn($wpUser);

        $timberUser = Mockery::mock(User::class); // Not a UserStub
        $timber = Mockery::mock('alias:' . Timber::class);
        $timber->shouldReceive('get_user')->once()->with($wpUser)->andReturn($timberUser);

        $controller = new class {
            public function handle(UserStub $user)
            {
            }
        };

        $this->expectException(\Rareloop\Lumberjack\Exceptions\MismatchedContextException::class);

        $this->invoker->call([$controller, 'handle']);
    }
}
