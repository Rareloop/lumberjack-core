<?php

namespace Rareloop\Lumberjack\Test;

use PHPUnit\Framework\TestCase;
use Rareloop\Lumberjack\Config;
use Tightenco\Collect\Support\Collection;

class CollectionsTest extends TestCase
{
    /** @test */
    public function can_create_collection_with_tighten_namespace()
    {
        $collection = new \Tightenco\Collect\Support\Collection(['foo', 'bar']);
        $this->assertEquals(2, $collection->count());
    }

    /** @test */
    public function can_create_collection_with_illuminate_namespace()
    {
        $collection = new \Illuminate\Support\Collection(['foo', 'bar']);

        $this->assertEquals(2, $collection->count());
    }

    /** @test */
    public function can_create_collection_with_collect_helper()
    {
        $collection = collect(['foo', 'bar']);

        $this->assertEquals(2, $collection->count());
    }
}
