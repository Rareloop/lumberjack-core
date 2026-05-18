<?php

namespace Rareloop\Lumberjack\Test\Unit\Http\Resolvers;

use Brain\Monkey\Functions;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Providers\WordPressControllersServiceProvider;
use Rareloop\Lumberjack\Test\TestCase;
use Rareloop\Lumberjack\Test\Unit\Concerns\BrainMonkeyPHPUnitIntegration;
use Rareloop\Lumberjack\Test\Unit\Http\Resolvers\Stubs\PostStub;
use Rareloop\Router\Invoker;
use Timber\Post;
use Timber\Timber;
use WP_Post;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class PostResolverTest extends TestCase
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
    public function it_can_resolve_a_timber_post(): void
    {
        $wpPost = Mockery::mock(WP_Post::class);
        Functions\expect('get_queried_object')->once()->andReturn($wpPost);

        $timberPost = Mockery::mock(Post::class);
        $timber = Mockery::mock('alias:' . Timber::class);
        $timber->shouldReceive('get_post')->once()->with($wpPost)->andReturn($timberPost);

        $controller = new class {
            public function handle(Post $post)
            {
                return $post;
            }
        };

        $result = $this->invoker->call([$controller, 'handle']);

        $this->assertSame($timberPost, $result);
    }

    #[Test]
    public function it_can_resolve_a_subclass_of_timber_post(): void
    {
        $wpPost = Mockery::mock(WP_Post::class);
        Functions\expect('get_queried_object')->once()->andReturn($wpPost);

        $postStub = Mockery::mock(PostStub::class);
        $timber = Mockery::mock('alias:' . Timber::class);
        $timber->shouldReceive('get_post')->once()->with($wpPost)->andReturn($postStub);

        $controller = new class {
            public function handle(PostStub $post)
            {
                return $post;
            }
        };

        $result = $this->invoker->call([$controller, 'handle']);

        $this->assertSame($postStub, $result);
    }
}
