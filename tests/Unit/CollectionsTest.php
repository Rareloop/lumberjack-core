<?php

namespace Rareloop\Lumberjack\Test;

use Rareloop\Lumberjack\Test\TestCase;
use PHPUnit\Framework\Attributes\Test;

class CollectionsTest extends TestCase
{
    #[Test]
    public function tighten_namespace_collection_no_longer_exists()
    {
        $this->assertFalse(class_exists('\Tightenco\Collect\Support\Collection'));
    }

    #[Test]
    public function can_create_collection_with_illuminate_namespace()
    {
        $collection = new \Illuminate\Support\Collection(['foo', 'bar']);

        $this->assertEquals(2, $collection->count());
    }

    #[Test]
    public function can_create_collection_with_collect_helper()
    {
        $collection = collect(['foo', 'bar']);

        $this->assertEquals(2, $collection->count());
    }
}
