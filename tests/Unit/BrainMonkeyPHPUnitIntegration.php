<?php

namespace Rareloop\Lumberjack\Test\Unit;

use Brain\Monkey;

trait BrainMonkeyPHPUnitIntegration
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Monkey\tearDown();
    }
}
