<?php

namespace Rareloop\Lumberjack\Test;

use PHPUnit\Framework\TestCase;
use Rareloop\Lumberjack\Page;

class PageTest extends TestCase
{
    /** @test */
    public function page_class_has_correct_post_type()
    {
        $this->assertSame('page', Page::getPostType());
    }
}
