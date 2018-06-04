<?php

namespace Rareloop\Lumberjack\Test;

use Mockery;
use PHPUnit\Framework\TestCase;
use Rareloop\Lumberjack\Helpers;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class GlobalFunctionsTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

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

    /**
     * @test
     * @dataProvider globalHelperFunctions
     */
    public function global_functions_proxy_calls_to_static_functions($function)
    {
        $helpers = Mockery::mock('alias:' . Helpers::class);
        $helpers->shouldReceive($function)->withArgs(['param1', 'param2'])->once();

        $function('param1', 'param2');
    }

    public static function globalHelperFunctions()
    {
        $reflection = new \ReflectionClass(Helpers::class);

        return collect($reflection->getMethods(\ReflectionMethod::IS_STATIC))->map(function ($function) {
            return [ $function->name ];
        })->toArray();
    }
}
