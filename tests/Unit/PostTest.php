<?php

namespace Rareloop\Lumberjack\Test;

use Brain\Monkey\Functions;
use Illuminate\Support\Collection;
use Mockery;
use PHPUnit\Framework\TestCase;
use Rareloop\Lumberjack\Post;
use Rareloop\Lumberjack\Test\Unit\BrainMonkeyPHPUnitIntegration;
use Timber\Post as TimberPost;
use Timber\Timber;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 * The above is required as we're using alias mocks which persist between tests
 * https://laracasts.com/discuss/channels/testing/mocking-a-class-persists-over-tests/replies/103075
 */
class PostTest extends TestCase
{
    use BrainMonkeyPHPUnitIntegration;

    /** @test */
    public function register_function_calls_register_post_type_when_post_type_and_config_are_provided()
    {
        Functions\expect('register_post_type')
            ->once()
            ->with(RegisterablePostType::getPostType(), RegisterablePostType::getPrivateConfig());

        RegisterablePostType::register();

        $this->assertNotFalse(has_filter('timber/post/classmap', [RegisterablePostType::class, 'filterTimberPostClassMap']));
    }

    /** @test */
    public function register_function_throws_exception_if_post_type_is_not_provided()
    {
        $this->expectException(\Rareloop\Lumberjack\Exceptions\PostTypeRegistrationException::class);
        UnregisterablePostTypeWithoutPostType::register();
    }

    /** @test */
    public function register_function_throws_exception_if_config_is_not_provided()
    {
        $this->expectException(\Rareloop\Lumberjack\Exceptions\PostTypeRegistrationException::class);

        UnregisterablePostTypeWithoutConfig::register();
    }

    /** @test */
    public function can_filter_timber_post_classmaps()
    {
        $output = Post::filterTimberPostClassMap(['another' => TimberPost::class]);

        $this->assertEqualsCanonicalizing(['another' => TimberPost::class, 'post' => Post::class], $output);

        $output = RegisterablePostType::filterTimberPostClassMap($output);

        $this->assertEqualsCanonicalizing(['another' => TimberPost::class, 'post' => Post::class, 'registerable_post_type' => RegisterablePostType::class], $output);
    }

    /**
     * @test
     */
    public function query_defaults_to_current_post_type_and_published()
    {
        $args = [
            'posts_per_page' => 10,
        ];

        $timber = Mockery::mock('alias:' . Timber::class);
        $timber->shouldReceive('get_posts')->withArgs([
            array_merge($args, [
                'post_type' => Post::getPostType(),
                'post_status' => 'publish',
            ]),
            Post::class,
        ])->once();

        $posts = Post::query($args);

        $this->assertInstanceOf(Collection::class, $posts);
    }

    /**
     * @test
     */
    public function query_ignores_passed_in_post_type()
    {
        $args = [
            'posts_per_page' => 10,
            'post_type' => 'something-else',
        ];

        $timber = Mockery::mock('alias:' . Timber::class);
        $timber->shouldReceive('get_posts')->withArgs([
            array_merge($args, [
                'post_type' => Post::getPostType(),
                'post_status' => 'publish',
            ]),
            Post::class,
        ])->once();

        $posts = Post::query($args);

        $this->assertInstanceOf(Collection::class, $posts);
    }

    /**
     * @test
     */
    public function post_subclass_query_has_correct_post_type()
    {
        $args = [
            'posts_per_page' => 10,
        ];

        $timber = Mockery::mock('alias:' . Timber::class);
        $timber->shouldReceive('get_posts')->withArgs([
            Mockery::subset([
                'post_type' => RegisterablePostType::getPostType(),
            ]),
            RegisterablePostType::class,
        ])->once();

        $posts = RegisterablePostType::query($args);

        $this->assertInstanceOf(Collection::class, $posts);
    }

    /**
     * @test
     */
    public function query_can_have_post_status_overwritten()
    {
        $args = [
            'post_status' => ['draft', 'publish'],
        ];

        $timber = Mockery::mock('alias:' . Timber::class);
        $timber->shouldReceive('get_posts')->withArgs([
            Mockery::subset([
                'post_status' => ['draft', 'publish'],
            ]),
            Post::class,
        ])->once();

        $posts = Post::query($args);

        $this->assertInstanceOf(Collection::class, $posts);
    }

    /**
     * @test
     */
    public function all_defaults_to_unlimited_ordered_by_menu_order_ascending()
    {
        $timber = Mockery::mock('alias:' . Timber::class);
        $timber->shouldReceive('get_posts')->withArgs([
            Mockery::subset([
                'posts_per_page' => -1,
                'orderby' => 'menu_order',
                'order' => 'ASC',
            ]),
            Post::class,
        ])->once();

        $posts = Post::all();

        $this->assertInstanceOf(Collection::class, $posts);
    }

    /**
     * @test
     */
    public function all_can_have_post_limit_set()
    {
        $timber = Mockery::mock('alias:' . Timber::class);
        $timber->shouldReceive('get_posts')->withArgs([
            Mockery::subset([
                'posts_per_page' => 10,
            ]),
            Post::class,
        ])->once();

        $posts = Post::all(10);

        $this->assertInstanceOf(Collection::class, $posts);
    }

    /**
     * @test
     */
    public function all_can_have_order_set()
    {
        $timber = Mockery::mock('alias:' . Timber::class);
        $timber->shouldReceive('get_posts')->withArgs([
            Mockery::subset([
                'orderby' => 'date',
                'order' => 'DESC',
            ]),
            Post::class,
        ])->once();

        $posts = Post::all(-1, 'date', 'DESC');

        $this->assertInstanceOf(Collection::class, $posts);
    }

    /**
     * @test
     */
    public function can_extend_post_behaviour_with_macros()
    {
        Post::macro('testFunctionAddedByMacro', function () {
            return 'abc123';
        });

        $post = new Post(null, true);

        $this->assertSame('abc123', $post->testFunctionAddedByMacro());
        $this->assertSame('abc123', Post::testFunctionAddedByMacro());
    }

    /**
     * @test
     */
    public function macros_set_correct_this_context_on_instances()
    {
        PostWithPrivateData::macro('testFunctionAddedByMacro', function () {
            return $this->dummyData;
        });

        $post = new PostWithPrivateData(null, true);

        $this->assertSame('abc123', $post->testFunctionAddedByMacro());
    }

    /**
     * @test
     */
    public function can_extend_post_behaviour_with_mixin()
    {
        Post::mixin(new PostMixin);

        $post = new Post(null, true);

        $this->assertSame('abc123', $post->testFunctionAddedByMixin());
    }
}

class PostMixin
{
    function testFunctionAddedByMixin()
    {
        return function () {
            return 'abc123';
        };
    }
}

class PostWithPrivateData extends Post
{
    private string $dummyData = 'abc123';
}

class RegisterablePostType extends Post
{
    public static function getPostType(): string
    {
        return 'registerable_post_type';
    }

    protected static function getPostTypeConfig(): array
    {
        return [
            'labels' => [
                'name' => 'Groups',
                'singular_name' => 'Group'
            ],
            'public' => true,
            'has_archive' => false,
            'supports' => ['title', 'revisions'],
            'menu_icon' => 'dashicons-groups',
            'rewrite' => [
                'slug' => 'group',
            ],
        ];
    }

    public static function getPrivateConfig()
    {
        return self::getPostTypeConfig();
    }
}

class UnregisterablePostTypeWithoutPostType extends Post
{
    protected static function getPostTypeConfig(): array
    {
        return [
            'labels' => [
                'name' => 'Groups',
                'singular_name' => 'Group'
            ],
            'public' => true,
            'has_archive' => false,
            'supports' => ['title', 'revisions'],
            'menu_icon' => 'dashicons-groups',
            'rewrite' => [
                'slug' => 'group',
            ],
        ];
    }
}

class UnregisterablePostTypeWithoutConfig extends Post
{
    public static function getPostType(): string
    {
        return 'post_type';
    }
}
