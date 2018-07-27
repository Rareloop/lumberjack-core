<?php

namespace Rareloop\Lumberjack\Test\QueryBuilder;

use Illuminate\Support\Collection;
use Mockery;
use PHPUnit\Framework\TestCase;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\QueryBuilder\Contracts\QueryBuilder as QueryBuilderContract;
use Rareloop\Lumberjack\Post;
use Rareloop\Lumberjack\QueryBuilder\QueryBuilder;
use Rareloop\Lumberjack\QueryBuilder\ScopedQueryBuilder;
use Timber\Timber;

class ScopedQueryBuilderTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    public function setUp()
    {
        $this->app = new Application;
        $this->app->bind(QueryBuilderContract::class, QueryBuilder::class);

        parent::setUp();
    }

    /** @test */
    public function correct_post_type_is_set()
    {
        $builder = new ScopedQueryBuilder(Post::class);
        $params = $builder->getParameters();

        $this->assertArraySubset([
            'post_type' => Post::getPostType(),
        ], $params);
    }

    /**
     * @test
     * @expectedException Rareloop\Lumberjack\QueryBuilder\Exceptions\CannotRedeclarePostTypeOnQueryException
     */
    public function cannot_overwrite_post_type()
    {
        $builder = new ScopedQueryBuilder(PostWithQueryScope::class);
        $builder->wherePostType('test_post_type');
    }

    /**
     * @test
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function get_retrieves_list_of_posts_of_correct_type()
    {
        $posts = [new PostWithQueryScope(1, true), new PostWithQueryScope(2, true)];

        $timber = Mockery::mock('alias:' . Timber::class);
        $timber->shouldReceive('get_posts')->withArgs([
            Mockery::subset([
                'post_type' => PostWithQueryScope::getPostType(),
                'post_status' => 'publish',
                'offset' => 10,
            ]),
            PostWithQueryScope::class,
        ])->once()->andReturn($posts);

        $builder = new ScopedQueryBuilder(PostWithQueryScope::class);
        $returnedPosts = $builder->whereStatus('publish')->offset(10)->get();

        $this->assertInstanceOf(Collection::class, $returnedPosts);
        $this->assertSame($posts, $returnedPosts->toArray());
    }

    /** @test */
    public function can_call_a_query_scope_on_post_object()
    {
        $builder = new ScopedQueryBuilder(PostWithQueryScope::class);
        $chainedBuilder = $builder->inDraft();
        $params = $builder->getParameters();

        $this->assertSame($builder, $chainedBuilder);
        $this->assertArraySubset([
            'post_status' => 'draft',
        ], $params);
    }

    /**
     * @test
     * @expectedException ErrorException
     */
    public function missing_query_scope_throws_an_error()
    {
        $builder = new ScopedQueryBuilder(PostWithQueryScope::class);
        $builder->nonExistentScope();
    }

    /** @test */
    public function can_use_a_different_query_builder_implementation()
    {
        $this->app->bind(QueryBuilderContract::class, CustomQueryBuilder::class);

        $builder = new ScopedQueryBuilder(Post::class);

        $this->assertSame('it works', $builder->nonStandardMethod());
    }
}

class CustomQueryBuilder extends QueryBuilder
{
    public function nonStandardMethod()
    {
        return 'it works';
    }
}

class PostWithQueryScope extends Post
{
    public static function getPostType()
    {
        return 'post_with_query_scope';
    }

    public function scopeInDraft($query)
    {
        return $query->whereStatus('draft');
    }
}
