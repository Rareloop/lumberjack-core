<?php

namespace Rareloop\Lumberjack\Test\Unit\Http\Resolvers;

use Brain\Monkey\Functions;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\PostType as LumberjackPostType;
use Rareloop\Lumberjack\Providers\WordPressControllersServiceProvider;
use Rareloop\Lumberjack\Test\TestCase;
use Rareloop\Lumberjack\Test\Unit\Concerns\BrainMonkeyPHPUnitIntegration;
use Rareloop\Router\Invoker;
use Timber\PostType as TimberPostType;

class PostTypeResolverTest extends TestCase
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
    public function it_can_resolve_a_post_type_from_a_post_type_object(): void
    {
        $wpPostType = Mockery::mock('WP_Post_Type');
        $wpPostType->name = 'page';
        Functions\expect('get_queried_object')->once()->andReturn($wpPostType);

        // Timber\PostType constructor calls get_post_type_object
        Functions\expect('get_post_type_object')->once()->with('page')->andReturn($wpPostType);

        $controller = new class {
            public function handle(LumberjackPostType $postType)
            {
                return $postType;
            }
        };

        $result = $this->invoker->call([$controller, 'handle']);

        $this->assertInstanceOf(LumberjackPostType::class, $result);
        $this->assertSame('page', (string)$result);
    }

    #[Test]
    public function it_can_resolve_a_post_type_from_a_post_object(): void
    {
        $wpPost = Mockery::mock('WP_Post');
        $wpPost->post_type = 'post';
        Functions\expect('get_queried_object')->once()->andReturn($wpPost);

        $wpPostTypeObject = Mockery::mock('WP_Post_Type');
        $wpPostTypeObject->name = 'post';

        // Called once by our resolver and once by the Timber\PostType constructor
        Functions\expect('get_post_type_object')->twice()->with('post')->andReturn($wpPostTypeObject);

        $controller = new class {
            public function handle(LumberjackPostType $postType)
            {
                return $postType;
            }
        };

        $result = $this->invoker->call([$controller, 'handle']);

        $this->assertInstanceOf(LumberjackPostType::class, $result);
        $this->assertSame('post', (string)$result);
    }

    #[Test]
    public function it_can_resolve_a_timber_post_type(): void
    {
        $wpPostType = Mockery::mock('WP_Post_Type');
        $wpPostType->name = 'page';
        Functions\expect('get_queried_object')->once()->andReturn($wpPostType);

        Functions\expect('get_post_type_object')->once()->with('page')->andReturn($wpPostType);

        $controller = new class {
            public function handle(TimberPostType $postType)
            {
                return $postType;
            }
        };

        $result = $this->invoker->call([$controller, 'handle']);

        $this->assertInstanceOf(TimberPostType::class, $result);
        $this->assertSame('page', (string)$result);
    }

    #[Test]
    public function it_throws_an_exception_if_the_context_is_missing(): void
    {
        Functions\expect('get_queried_object')->once()->andReturn(null);

        $controller = new class {
            public function handle(LumberjackPostType $postType)
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
            public function handle(?LumberjackPostType $postType)
            {
                return $postType;
            }
        };

        $result = $this->invoker->call([$controller, 'handle']);

        $this->assertNull($result);
    }
}
