<?php

namespace Rareloop\Lumberjack\Test\Facades;

use Mockery;
use Rareloop\Lumberjack\Test\TestCase;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\FacadeFactory;
use Rareloop\Lumberjack\Facades\Ignition;
use Spatie\Ignition\Ignition as SpatieIgnition;
use PHPUnit\Framework\Attributes\Test;

class IgnitionTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    #[Test]
    public function it_should_return_the_correct_accessor()
    {
        $app = new Application();
        FacadeFactory::setContainer($app);

        $ignition = Mockery::mock(SpatieIgnition::class);
        $app->singleton(SpatieIgnition::class, $ignition);

        $this->assertSame($ignition, Ignition::__instance());
    }
}
