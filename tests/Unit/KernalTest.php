<?php

namespace Rareloop\Lumberjack\Test;

use Brain\Monkey;
use Brain\Monkey\Actions;
use PHPUnit\Framework\TestCase;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Http\Kernal;

class KernalTest extends TestCase
{
    public function setUp() {
        parent::setUp();
        Monkey\setUp();
    }
    public function tearDown() {
        parent::tearDown();
        Monkey\tearDown();
    }

    /** @test */
    public function creating_kernal_should_bind_action_to_after_theme_setup_action()
    {
        $app = new Application();

        $kernal = new TestKernal($app);

        $this->assertTrue(has_action('after_theme_setup', [$kernal, 'bootstrap']));
    }
}

class TestKernal extends Kernal {}
