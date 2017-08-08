<?php

namespace Rareloop\Lumberjack\Test\Unit;

use Brain\Monkey;

trait BrainMonkeyPHPUnitIntegration
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    public function setUp()
    {
        parent::setUp();
        Monkey\setUp();
    }

    public function tearDown()
    {
        parent::tearDown();
        Monkey\tearDown();
    }
}
