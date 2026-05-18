<?php

namespace Rareloop\Lumberjack\Test\Unit\Http\Resolvers;

use Brain\Monkey\Functions;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Providers\WordPressControllersServiceProvider;
use Rareloop\Lumberjack\Test\TestCase;
use Rareloop\Lumberjack\Test\Unit\Concerns\BrainMonkeyPHPUnitIntegration;
use Rareloop\Lumberjack\Test\Unit\Http\Resolvers\Stubs\PostQueryStub;
use Rareloop\Router\Invoker;
use Timber\PostQuery;
use WP_Query;

class PostQueryResolverTest extends TestCase
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
    public function it_can_resolve_a_timber_post_query(): void
    {
        $wpQuery = $this->mockWpQuery();
        $this->app->bind(WP_Query::class, $wpQuery);

        $controller = new class {
            public function handle(\Timber\PostQuery $query)
            {
                return $query;
            }
        };

        $result = $this->invoker->call([$controller, 'handle']);

        $this->assertInstanceOf(\Timber\PostQuery::class, $result);
    }

    #[Test]
    public function it_can_resolve_a_post_collection_interface(): void
    {
        $wpQuery = $this->mockWpQuery();
        $this->app->bind(WP_Query::class, $wpQuery);

        $controller = new class {
            public function handle(\Timber\PostCollectionInterface $query)
            {
                return $query;
            }
        };

        $result = $this->invoker->call([$controller, 'handle']);

        $this->assertInstanceOf(\Timber\PostCollectionInterface::class, $result);
    }

    #[Test]
    public function it_can_resolve_a_subclass_of_timber_post_query(): void
    {
        $wpQuery = $this->mockWpQuery();
        $this->app->bind(WP_Query::class, $wpQuery);

        $controller = new class {
            public function handle(PostQueryStub $query)
            {
                return $query;
            }
        };

        $result = $this->invoker->call([$controller, 'handle']);

        $this->assertInstanceOf(PostQueryStub::class, $result);
    }

    protected function mockWpQuery()
    {
        Functions\expect('wp_parse_args')->andReturnUsing(fn($a, $b) => array_merge($b, $a));

        $wpQuery = Mockery::mock(WP_Query::class);
        $wpQuery->found_posts = 0;
        $wpQuery->posts = [];

        return $wpQuery;
    }
}
