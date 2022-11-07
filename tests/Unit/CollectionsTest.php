<?php

namespace Rareloop\Lumberjack\Test;

use PHPUnit\Framework\TestCase;

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

    /** @test */
    public function function_signatures_returning_collections_are_interchangeable()
    {
        $illuminate = new \Illuminate\Support\Collection(['foo', 'bar']);
        $tighten = new \Tightenco\Collect\Support\Collection(['foo', 'bar']);

        // Functions returning Illuminate namespace are happy with the current implementation
        $this->assertEquals(2, CollectionsTester::returnAsIlluminate($illuminate)->count());
        $this->assertEquals(2, CollectionsTester::returnAsIlluminate($tighten)->count());

        // Functions returning the legacy Tightenco namespace are happy with the current implementation
        $this->assertEquals(2, CollectionsTester::returnAsTighten($illuminate)->count());
        $this->assertEquals(2, CollectionsTester::returnAsTighten($tighten)->count());
    }
}

class CollectionsTester
{
    public static function returnAsTighten($collection): \Tightenco\Collect\Support\Collection
    {
        return $collection;
    }

    public static function returnAsIlluminate($collection): \Illuminate\Support\Collection
    {
        return $collection;
    }
}
