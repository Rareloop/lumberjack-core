<?php

namespace Rareloop\Lumberjack\Test\Unit;

use Brain\Monkey;

trait BrainMonkeyPHPUnitIntegration
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    public function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();
    }

    public function tearDown(): void
    {
        parent::tearDown();
        Monkey\tearDown();
    }
}
