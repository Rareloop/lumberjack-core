<?php

namespace Rareloop\Lumberjack\Test;

use Illuminate\Support\Collection;
use Mockery;
use PHPUnit\Framework\TestCase;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Contracts\QueryBuilder as QueryBuilderContract;
use Rareloop\Lumberjack\Post;
use Rareloop\Lumberjack\QueryBuilder;
use Rareloop\Lumberjack\ScopedQueryBuilder;
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
     * @expectedException Rareloop\Lumberjack\Exceptions\CannotRedeclarePostTypeOnQueryException
     */
    public function cannot_overwrite_post_type()
    {
        $builder = new ScopedQueryBuilder(PostWithQueryScope::class);
        $builder->wherePostType('test_post_type');
    }

    /**
     * @test
     * @expectedException Rareloop\Lumberjack\Exceptions\CannotRedeclarePostClassOnQueryException
     */
    public function cannot_overwrite_post_class()
    {
        $builder = new ScopedQueryBuilder(PostWithQueryScope::class);
        $builder->as(Post::class);
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

    /** @test */
    public function can_pass_params_into_a_query_scope_on_post_object()
    {
        $builder = new ScopedQueryBuilder(PostWithQueryScope::class);
        $chainedBuilder = $builder->without(1, 2);
        $params = $builder->getParameters();

        $this->assertSame($builder, $chainedBuilder);
        $this->assertArraySubset([
            'post__not_in' => [1, 2],
        ], $params);
    }

    /**
     * @test
     * @runInSeparateProcess
     * @expectedException \PHPUnit\Framework\Error\Error
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

    /** @test */
    public function can_call_a_function_added_to_querybuilder_via_a_macro()
    {
        QueryBuilder::macro('testFunctionAddedByMacro', function () {
            $this->params['foo'] = 'bar';

            return $this;
        });

        $builder = new ScopedQueryBuilder(Post::class);
        $chainedBuilder = $builder->testFunctionAddedByMacro();
        $params = $builder->getParameters();

        $this->assertSame($builder, $chainedBuilder);
        $this->assertArraySubset([
            'foo' => 'bar',
        ], $params);
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

    public function scopeWithout($query, $id1, $id2)
    {
        return $query->whereIdNotIn([$id1, $id2]);
    }
}

