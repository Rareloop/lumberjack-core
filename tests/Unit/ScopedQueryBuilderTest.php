<?php

namespace Rareloop\Lumberjack\Test;

use Mockery;
use Throwable;
use Timber\Timber;
use Timber\PostQuery;
use Rareloop\Lumberjack\Post;
use Rareloop\Lumberjack\Test\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Support\Collection;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\QueryBuilder;
use Rareloop\Lumberjack\ScopedQueryBuilder;
use Rareloop\Lumberjack\Contracts\QueryBuilder as QueryBuilderContract;
use Rareloop\Lumberjack\Test\Unit\Concerns\ArraySubsetAsserts;

use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\Attributes\PreserveGlobalState;

class ScopedQueryBuilderTest extends TestCase
{
    /**
     * @var array<string, string>
     */
    public $params;
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration,
        ArraySubsetAsserts;

    private Application $app;

    public function setUp(): void
    {
        $this->app = new Application;
        $this->app->bind(QueryBuilderContract::class, QueryBuilder::class);

        parent::setUp();
    }

    #[Test]
    public function correct_post_type_is_set(): void
    {
        $builder = new ScopedQueryBuilder(Post::class);
        $params = $builder->getParameters();

        $this->assertArraySubset([
            'post_type' => Post::getPostType(),
        ], $params);
    }

    #[Test]
    public function cannot_overwrite_post_type(): void
    {
        $this->expectException(\Rareloop\Lumberjack\Exceptions\CannotRedeclarePostTypeOnQueryException::class);

        $builder = new ScopedQueryBuilder(PostWithQueryScope::class);
        $builder->wherePostType('test_post_type');
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    #[Test]
    public function get_retrieves_list_of_posts(): void
    {
        // $posts = [new PostWithQueryScope(1, true), new PostWithQueryScope(2, true)];

        $postQuery = Mockery::mock(PostQuery::class);
        $postQuery->shouldReceive('to_array')->once()->andReturn([123, 'abc']);

        $timber = Mockery::mock('alias:' . Timber::class);
        $timber->shouldReceive('get_posts')->withArgs([
            Mockery::subset([
                'post_type' => PostWithQueryScope::getPostType(),
                'post_status' => 'publish',
                'offset' => 10,
            ]),
        ])->once()->andReturn($postQuery);

        $builder = new ScopedQueryBuilder(PostWithQueryScope::class);
        $returnedPosts = $builder->whereStatus('publish')->offset(10)->get();

        $this->assertInstanceOf(Collection::class, $returnedPosts);
        $this->assertSame([123, 'abc'], $returnedPosts->toArray());
    }

    #[Test]
    public function can_call_a_query_scope_on_post_object(): void
    {
        $builder = new ScopedQueryBuilder(PostWithQueryScope::class);
        $chainedBuilder = $builder->inDraft();
        $params = $builder->getParameters();

        $this->assertSame($builder, $chainedBuilder);
        $this->assertArraySubset([
            'post_status' => 'draft',
        ], $params);
    }

    #[Test]
    public function can_pass_params_into_a_query_scope_on_post_object(): void
    {
        $builder = new ScopedQueryBuilder(PostWithQueryScope::class);
        $chainedBuilder = $builder->without(1, 2);
        $params = $builder->getParameters();

        $this->assertSame($builder, $chainedBuilder);
        $this->assertArraySubset([
            'post__not_in' => [1, 2],
        ], $params);
    }

    #[Test]
    public function missing_query_scope_throws_an_error(): void
    {
        $this->expectUserError();
        $this->expectException(\ErrorException::class);

        $builder = new ScopedQueryBuilder(PostWithQueryScope::class);
        $builder->nonExistentScope();
    }

    #[Test]
    public function can_use_a_different_query_builder_implementation(): void
    {
        $this->app->bind(QueryBuilderContract::class, CustomQueryBuilder::class);

        $builder = new ScopedQueryBuilder(Post::class);

        $this->assertSame('it works', $builder->nonStandardMethod());
    }

    #[Test]
    public function can_call_a_function_added_to_querybuilder_via_a_macro(): void
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
    #[\Override]
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
