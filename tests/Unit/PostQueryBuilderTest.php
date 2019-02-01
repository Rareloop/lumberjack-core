<?php

namespace Rareloop\Lumberjack\Test;

use Mockery;
use PHPUnit\Framework\TestCase;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Contracts\QueryBuilder as QueryBuilderContract;
use Rareloop\Lumberjack\Post;
use Rareloop\Lumberjack\QueryBuilder;
use Rareloop\Lumberjack\ScopedQueryBuilder;

class PostQueryBuilderTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    public function setUp()
    {
        $this->app = new Application;
        $this->app->bind(QueryBuilderContract::class, QueryBuilder::class);

        parent::setUp();
    }

    /** @test */
    public function can_create_a_builder()
    {
        $builder = Post::builder();

        $this->assertInstanceOf(ScopedQueryBuilder::class, $builder);
        $this->assertArraySubset([
            'post_type' => Post::getPostType(),
        ], $builder->getParameters());
    }

    /** @test */
    public function can_create_a_builder_from_static_functions()
    {
        $this->assertQueryBuilder('whereStatus', ['publish'], QueryBuilderTestPost::class);
        $this->assertQueryBuilder('whereIdIn', [[1, 2, 3]], QueryBuilderTestPost::class);
        $this->assertQueryBuilder('whereIdNotIn', [[1, 2, 3]], QueryBuilderTestPost::class);
    }

    /**
     * @test
     * @runInSeparateProcess
     * @expectedException \PHPUnit\Framework\Error\Error
     */
    public function throw_error_on_missing_static_function()
    {
        Post::missingStaticFunction();
    }

    private function assertQueryBuilder($function, $params, $postType)
    {
        $builder = Mockery::mock(ScopedQueryBuilder::class.'['.$function.']', [$postType]);
        $builder->shouldReceive($function)->withArgs($params)->once();

        // Inject the mock builder
        call_user_func([$postType, 'setCreateBuilderResponse'], $builder);

        // Call the static function e.g. $postType::$function($params)
        call_user_func_array([$postType, $function], $params);
    }
}

class QueryBuilderTestPost extends Post
{
    private static $injectedBuilder;

    public static function setCreateBuilderResponse($builder)
    {
        static::$injectedBuilder = $builder;
    }

    public static function builder() : ScopedQueryBuilder
    {
        return static::$injectedBuilder;
    }
}
