<?php

namespace Rareloop\Lumberjack\Test;

use Mockery;
use Rareloop\Lumberjack\Test\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Rareloop\Lumberjack\Helpers;

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\Attributes\PreserveGlobalState;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class GlobalFunctionsTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    public function setUp(): void
    {
        include_once(__DIR__ . '/../../src/functions.php');

        parent::setUp();
    }

    #[Test]
    #[DataProvider('globalHelperFunctions')]
    public function global_functions_are_registered($function)
    {
        $this->assertTrue(function_exists($function));
    }

    #[Test]
    #[DataProvider('globalHelperFunctions')]
    public function global_functions_proxy_calls_to_static_functions($function)
    {
        $helpers = Mockery::mock('alias:' . Helpers::class);
        $helpers->shouldReceive($function)->withArgs(['param1', 'param2'])->once();

        $function('param1', 'param2');
    }

    public static function globalHelperFunctions(): array
    {
        $reflection = new \ReflectionClass(Helpers::class);

        return collect($reflection->getMethods(\ReflectionMethod::IS_STATIC))->map(function ($function) {
            return [$function->name];
        })->toArray();
    }
}
