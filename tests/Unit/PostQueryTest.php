<?php

namespace Rareloop\Lumberjack\Test\Unit;

use PHPUnit\Framework\Attributes\Test;
use Rareloop\Lumberjack\PostQuery;
use Timber\PostQuery as TimberPostQuery;
use Rareloop\Lumberjack\Test\TestCase;
use Rareloop\Lumberjack\Test\Unit\Concerns\BrainMonkeyPHPUnitIntegration;
use Mockery;
use WP_Query;

class PostQueryTest extends TestCase
{
    use BrainMonkeyPHPUnitIntegration;

    #[Test]
    public function it_extends_the_timber_post_query_class(): void
    {
        $wpQuery = Mockery::mock(WP_Query::class);
        $wpQuery->found_posts = 0;
        $wpQuery->posts = [];
        
        $postQuery = new PostQuery($wpQuery);

        $this->assertInstanceOf(TimberPostQuery::class, $postQuery);
    }

    #[Test]
    public function can_extend_post_query_with_macros(): void
    {
        PostQuery::macro('testMacro', function () {
            return 'macro_result';
        });

        $wpQuery = Mockery::mock(WP_Query::class);
        $wpQuery->found_posts = 0;
        $wpQuery->posts = [];

        $postQuery = new PostQuery($wpQuery);

        $this->assertSame('macro_result', $postQuery->testMacro());
    }
}
