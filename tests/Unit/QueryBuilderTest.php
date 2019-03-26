<?php

namespace Rareloop\Lumberjack\Test;

use Illuminate\Support\Collection;
use Mockery;
use PHPUnit\Framework\TestCase;
use Rareloop\Lumberjack\Post;
use Rareloop\Lumberjack\QueryBuilder;
use Timber\Timber;

class QueryBuilderTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    /** @test */
    public function correct_post_type_is_set()
    {
        $builder = new QueryBuilder();
        $chainedBuilder = $builder->wherePostType('test_post_type');
        $params = $builder->getParameters();

        $this->assertArraySubset([
            'post_type' => 'test_post_type',
        ], $params);
    }

    /** @test */
    public function can_limit_post_count()
    {
        $builder = new QueryBuilder();
        $chainedBuilder = $builder->limit(10);
        $params = $builder->getParameters();

        $this->assertSame($builder, $chainedBuilder);
        $this->assertArraySubset([
            'posts_per_page' => 10,
        ], $params);
    }

    /** @test */
    public function can_set_post_offset()
    {
        $builder = new QueryBuilder();
        $chainedBuilder = $builder->offset(10);
        $params = $builder->getParameters();

        $this->assertSame($builder, $chainedBuilder);
        $this->assertArraySubset([
            'offset' => 10,
        ], $params);
    }

    /** @test */
    public function can_set_order_desc()
    {
        $builder = new QueryBuilder();
        $chainedBuilder = $builder->orderBy('menu_order', QueryBuilder::DESC);
        $params = $builder->getParameters();

        $this->assertSame($builder, $chainedBuilder);
        $this->assertArraySubset([
            'orderby' => 'menu_order',
            'order' => 'DESC',
        ], $params);
    }

    /** @test */
    public function can_set_order_asc()
    {
        $builder = new QueryBuilder();
        $chainedBuilder = $builder->orderBy('menu_order', QueryBuilder::ASC);
        $params = $builder->getParameters();

        $this->assertSame($builder, $chainedBuilder);
        $this->assertArraySubset([
            'orderby' => 'menu_order',
            'order' => 'ASC',
        ], $params);
    }

    /** @test */
    public function can_set_order_by_meta()
    {
        $builder = new QueryBuilder();
        $chainedBuilder = $builder->orderByMeta('test_meta_key', QueryBuilder::DESC);
        $params = $builder->getParameters();

        $this->assertSame($builder, $chainedBuilder);
        $this->assertArraySubset([
            'orderby' => 'meta_value',
            'meta_key' => 'test_meta_key',
            'order' => 'DESC',
        ], $params);
    }

    /** @test */
    public function can_set_order_by_meta_with_numeric_ordering()
    {
        $builder = new QueryBuilder();
        $chainedBuilder = $builder->orderByMeta('test_meta_key', QueryBuilder::DESC, QueryBuilder::NUMERIC);
        $params = $builder->getParameters();

        $this->assertSame($builder, $chainedBuilder);
        $this->assertArraySubset([
            'orderby' => 'meta_value_num',
            'meta_key' => 'test_meta_key',
            'order' => 'DESC',
        ], $params);
    }

    /** @test */
    public function can_restrict_to_ids_in_array()
    {
        $builder = new QueryBuilder();
        $chainedBuilder = $builder->whereIdIn([1, 2, 3]);
        $params = $builder->getParameters();

        $this->assertSame($builder, $chainedBuilder);
        $this->assertArraySubset([
            'post__in' => [1, 2, 3],
        ], $params);
    }

    /** @test */
    public function can_filter_by_status()
    {
        $builder = new QueryBuilder();
        $chainedBuilder = $builder->whereStatus('publish');
        $params = $builder->getParameters();

        $this->assertSame($builder, $chainedBuilder);
        $this->assertArraySubset([
            'post_status' => 'publish',
        ], $params);
    }

    /** @test */
    public function can_filter_by_multiple_statuses_as_array()
    {
        $builder = new QueryBuilder();
        $chainedBuilder = $builder->whereStatus(['publish', 'draft']);
        $params = $builder->getParameters();

        $this->assertSame($builder, $chainedBuilder);
        $this->assertArraySubset([
            'post_status' => ['publish', 'draft'],
        ], $params);
    }

    /** @test */
    public function can_filter_by_multiple_statuses_as_multiple_params()
    {
        $builder = new QueryBuilder();
        $chainedBuilder = $builder->whereStatus('publish', 'draft');
        $params = $builder->getParameters();

        $this->assertSame($builder, $chainedBuilder);
        $this->assertArraySubset([
            'post_status' => ['publish', 'draft'],
        ], $params);
    }

    /**
     * @test
     * @expectedException     InvalidArgumentException
     */
    public function calling_where_status_without_params_throws_an_exception()
    {
        $builder = new QueryBuilder();
        $chainedBuilder = $builder->whereStatus();
    }

    /** @test */
    public function can_add_a_single_meta_query()
    {
        $builder = new QueryBuilder();
        $chainedBuilder = $builder->whereMeta('test_meta_key', 'test_meta_value');
        $params = $builder->getParameters();

        $this->assertSame($builder, $chainedBuilder);
        $this->assertArraySubset([
            'meta_query' => [
                [
                    'key' => 'test_meta_key',
                    'value' => 'test_meta_value',
                    'compare' => '=',
                ]
            ],
        ], $params);
    }

    /** @test */
    public function can_add_multiple_meta_queries()
    {
        $builder = new QueryBuilder();
        $builder->whereMeta('test_meta_key1', 'test_meta_value1');
        $builder->whereMeta('test_meta_key2', 'test_meta_value2');
        $params = $builder->getParameters();

        $this->assertArraySubset([
            'meta_query' => [
                [
                    'key' => 'test_meta_key1',
                    'value' => 'test_meta_value1',
                    'compare' => '=',
                ],
                [
                    'key' => 'test_meta_key2',
                    'value' => 'test_meta_value2',
                    'compare' => '=',
                ]
            ],
        ], $params);
    }

    /** @test */
    public function can_set_comparison_operator_on_meta_query()
    {
        $builder = new QueryBuilder();
        $chainedBuilder = $builder->whereMeta('test_meta_key', 'test_meta_value', '>=');
        $params = $builder->getParameters();

        $this->assertSame($builder, $chainedBuilder);
        $this->assertArraySubset([
            'meta_query' => [
                [
                    'key' => 'test_meta_key',
                    'value' => 'test_meta_value',
                    'compare' => '>=',
                ]
            ],
        ], $params);
    }

    /** @test */
    public function can_set_type_on_meta_query()
    {
        $builder = new QueryBuilder();
        $chainedBuilder = $builder->whereMeta('test_meta_key', 'test_meta_value', '>=', 'NUMERIC');
        $params = $builder->getParameters();

        $this->assertSame($builder, $chainedBuilder);
        $this->assertArraySubset([
            'meta_query' => [
                [
                    'key' => 'test_meta_key',
                    'value' => 'test_meta_value',
                    'compare' => '>=',
                    'type' => 'NUMERIC',
                ]
            ],
        ], $params);
    }

    /** @test */
    public function can_set_meta_query_relation_or()
    {
        $builder = new QueryBuilder();
        $chainedBuilder = $builder->whereMetaRelationshipIs(QueryBuilder::OR);
        $builder->whereMeta('test_meta_key1', 'test_meta_value1');
        $builder->whereMeta('test_meta_key2', 'test_meta_value2');
        $params = $builder->getParameters();

        $this->assertSame($builder, $chainedBuilder);
        $this->assertArraySubset([
            'meta_query' => [
                'relation' => 'OR',
                [
                    'key' => 'test_meta_key1',
                    'value' => 'test_meta_value1',
                    'compare' => '=',
                ],
                [
                    'key' => 'test_meta_key2',
                    'value' => 'test_meta_value2',
                    'compare' => '=',
                ]
            ],
        ], $params);
    }

    /** @test */
    public function can_set_meta_query_relation_and()
    {
        $builder = new QueryBuilder();
        $chainedBuilder = $builder->whereMetaRelationshipIs(QueryBuilder::AND);
        $builder->whereMeta('test_meta_key1', 'test_meta_value1');
        $builder->whereMeta('test_meta_key2', 'test_meta_value2');
        $params = $builder->getParameters();

        $this->assertSame($builder, $chainedBuilder);
        $this->assertArraySubset([
            'meta_query' => [
                'relation' => 'AND',
                [
                    'key' => 'test_meta_key1',
                    'value' => 'test_meta_value1',
                    'compare' => '=',
                ],
                [
                    'key' => 'test_meta_key2',
                    'value' => 'test_meta_value2',
                    'compare' => '=',
                ]
            ],
        ], $params);
    }

    /**
     * @test
     * @expectedException     Rareloop\Lumberjack\Exceptions\InvalidMetaRelationshipException
     */
    public function invalid_meta_realtionship_throws_an_exception()
    {
        $builder = new QueryBuilder();
        $builder->whereMetaRelationshipIs('INVALID');
    }

    /**
     * @test
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function get_retrieves_list_of_posts()
    {
        $posts = [new Post(1, true), new Post(2, true)];

        $timber = Mockery::mock('alias:' . Timber::class);
        $timber
            ->shouldReceive('get_posts')
            ->withArgs([
                Mockery::subset([
                    'post_status' => 'publish',
                    'offset' => 10,
                ]),
                Post::class,
            ])
            ->once()
            ->andReturn($posts);

        $builder = new QueryBuilder();
        $returnedPosts = $builder->whereStatus('publish')->offset(10)->get();

        $this->assertInstanceOf(Collection::class, $returnedPosts);
        $this->assertSame($posts, $returnedPosts->toArray());
    }

    /**
     * @test
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function get_retrieves_empty_collection_when_timber_returns_false()
    {
        $timber = Mockery::mock('alias:' . Timber::class);
        $timber
            ->shouldReceive('get_posts')
            ->withArgs([
                Mockery::subset([
                    'post_status' => 'publish',
                    'offset' => 10,
                ]),
                Post::class,
            ])
            ->once()
            ->andReturn(false);

        $builder = new QueryBuilder();
        $returnedPosts = $builder->whereStatus('publish')->offset(10)->get();

        $this->assertInstanceOf(Collection::class, $returnedPosts);
        $this->assertSame(0, $returnedPosts->count());
    }

    /**
     * @test
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function get_retrieves_empty_collection_when_timber_returns_null()
    {
        $timber = Mockery::mock('alias:' . Timber::class);
        $timber
            ->shouldReceive('get_posts')
            ->withArgs([
                Mockery::subset([
                    'post_status' => 'publish',
                    'offset' => 10,
                ]),
                Post::class,
            ])
            ->once()
            ->andReturn(null);

        $builder = new QueryBuilder();
        $returnedPosts = $builder->whereStatus('publish')->offset(10)->get();

        $this->assertInstanceOf(Collection::class, $returnedPosts);
        $this->assertSame(0, $returnedPosts->count());
    }

    /**
     * @test
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function can_specify_the_class_type_to_return()
    {
        $timber = Mockery::mock('alias:' . Timber::class);
        $timber
            ->shouldReceive('get_posts')
            ->withArgs([
                Mockery::subset([
                    'post_status' => 'publish',
                    'offset' => 10,
                ]),
                PostWithCustomPostType::class,
            ])
            ->once();

        $builder = new QueryBuilder();
        $builder->whereStatus('publish')->offset(10)->as(PostWithCustomPostType::class)->get();
    }

    /**
     * @test
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function first_retrieves_first_relevant_match()
    {
        $post = new Post(1, true);

        $timber = Mockery::mock('alias:' . Timber::class);
        $timber
            ->shouldReceive('get_posts')
            ->withArgs([
                Mockery::subset([
                    'post_status' => 'publish',
                    'limit' => 1,
                ]),
                Post::class,
            ])
            ->once()
            ->andReturn([$post]);

        $builder = new QueryBuilder();
        $returnedPost = $builder->whereStatus('publish')->first();

        $this->assertInstanceOf(Post::class, $returnedPost);
        $this->assertSame($post, $returnedPost);
    }

    /**
     * @test
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function first_returns_null_if_no_matching_post()
    {
        $post = new Post(1, true);

        $timber = Mockery::mock('alias:' . Timber::class);
        $timber
            ->shouldReceive('get_posts')
            ->withArgs([
                Mockery::subset([
                    'post_status' => 'publish',
                    'limit' => 1,
                ]),
                Post::class,
            ])
            ->once()
            ->andReturn([]);

        $builder = new QueryBuilder();
        $returnedPost = $builder->whereStatus('publish')->first();

        $this->assertSame(null, $returnedPost);
    }

    /**
     * @test
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function first_returns_null_when_timber_returns_false()
    {
        $post = new Post(1, true);

        $timber = Mockery::mock('alias:' . Timber::class);
        $timber
            ->shouldReceive('get_posts')
            ->withArgs([
                Mockery::subset([
                    'post_status' => 'publish',
                    'limit' => 1,
                ]),
                Post::class,
            ])
            ->once()
            ->andReturn(false);

        $builder = new QueryBuilder();
        $returnedPost = $builder->whereStatus('publish')->first();

        $this->assertSame(null, $returnedPost);
    }

    /**
     * @test
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function first_returns_null_when_timber_returns_null()
    {
        $post = new Post(1, true);

        $timber = Mockery::mock('alias:' . Timber::class);
        $timber
            ->shouldReceive('get_posts')
            ->withArgs([
                Mockery::subset([
                    'post_status' => 'publish',
                    'limit' => 1,
                ]),
                Post::class,
            ])
            ->once()
            ->andReturn(null);

        $builder = new QueryBuilder();
        $returnedPost = $builder->whereStatus('publish')->first();

        $this->assertSame(null, $returnedPost);
    }

    /** @test */
    public function can_clone_an_instance()
    {
        $builder1 = new QueryBuilder();
        $chainedBuilder = $builder1->limit(10);
        $params = $builder1->getParameters();

        $builder2 = $builder1->clone();

        $builder1->limit(20);

        $this->assertNotSame($builder1, $builder2);

        $this->assertArraySubset([
            'posts_per_page' => 20,
        ], $builder1->getParameters());

        $this->assertArraySubset([
            'posts_per_page' => 10,
        ], $builder2->getParameters());
    }

    /**
     * @test
     */
    public function can_extend_querybuilder_behaviour_with_macros()
    {
        QueryBuilder::macro('testFunctionAddedByMacro', function () {
            $this->params['foo'] = 'bar';

            return $this;
        });

        $queryBuilder = new QueryBuilder();

        $this->assertSame(['foo' => 'bar'], $queryBuilder->testFunctionAddedByMacro()->getParameters());
    }

    /**
     * @test
     */
    public function can_extend_querybuilder_behaviour_with_mixin()
    {
        QueryBuilder::mixin(new QueryBuilderMixin);

        $queryBuilder = new QueryBuilder();

        $this->assertSame(['foo' => 'bar'], $queryBuilder->testFunctionAddedByMixin()->getParameters());
    }

    // TODO: Test that undefined functions throw an appropriate error
}

class QueryBuilderMixin
{
    function testFunctionAddedByMixin()
    {
        return function() {
            $this->params['foo'] = 'bar';

            return $this;
        };
    }
}

class PostWithCustomPostType extends Post
{
    public static function getPostType()
    {
        return 'post_with_query_scope';
    }
}
