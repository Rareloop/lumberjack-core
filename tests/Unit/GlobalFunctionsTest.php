<?php

namespace Rareloop\Lumberjack\Test;

use PHPUnit\Framework\TestCase;
use Rareloop\Lumberjack\Helpers;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class GlobalFunctionsTest extends TestCase
{
    public function setUp()
    {
        include_once(__DIR__ . '/../../src/functions.php');

        parent::setUp();
    }

    /**
     * @test
     * @dataProvider globalHelperFunctions
     */
    public function global_functions_are_registered($function)
    {
        $this->assertTrue(function_exists($function));
    }

    public static function globalHelperFunctions()
    {
        $reflection = new \ReflectionClass(Helpers::class);

        return collect($reflection->getMethods(\ReflectionMethod::IS_STATIC))->map(function ($function) {
            return [ $function->name ];
        })->toArray();
    }
}
