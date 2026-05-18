<?php

namespace Rareloop\Lumberjack\Test\Unit;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use PHPUnit\Framework\Attributes\Test;
use Rareloop\Lumberjack\PostType;
use Timber\PostType as TimberPostType;
use Rareloop\Lumberjack\Test\TestCase;
use Rareloop\Lumberjack\Test\Unit\Concerns\BrainMonkeyPHPUnitIntegration;
use Mockery;

class PostTypeTest extends TestCase
{
    use BrainMonkeyPHPUnitIntegration;

    #[Test]
    public function it_extends_the_timber_post_type_class(): void
    {
        Functions\expect('get_post_type_object')->andReturn(Mockery::mock('WP_Post_Type'));
        $postType = new PostType('post');

        $this->assertInstanceOf(TimberPostType::class, $postType);
    }

    #[Test]
    public function can_get_post_class_associated_with_post_type(): void
    {
        $wpPostType = new \stdClass();
        $wpPostType->name = 'book';
        Functions\expect('get_post_type_object')->andReturn($wpPostType);
        
        $postType = new PostType('book');

        Filters\expectApplied('timber/post/classmap')
            ->once()
            ->andReturn(['book' => 'App\Post\Book']);

        $this->assertSame('App\Post\Book', $postType->postClass());
    }

    #[Test]
    public function can_extend_post_type_with_macros(): void
    {
        $wpPostType = new \stdClass();
        $wpPostType->name = 'post';
        Functions\expect('get_post_type_object')->andReturn($wpPostType);
        
        PostType::macro('testMacro', function () {
            return 'macro_result';
        });

        $postType = new PostType('post');

        $this->assertSame('macro_result', $postType->testMacro());
    }
}
