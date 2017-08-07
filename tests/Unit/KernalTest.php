<?php

namespace Rareloop\Lumberjack\Test;

use Brain\Monkey;
use Brain\Monkey\Actions;
use Mockery;
use PHPUnit\Framework\TestCase;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Bootstrappers\BootProviders;
use Rareloop\Lumberjack\Bootstrappers\LoadConfiguration;
use Rareloop\Lumberjack\Bootstrappers\RegisterProviders;
use Rareloop\Lumberjack\Http\Kernal;

class KernalTest extends TestCase
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

    /** @test */
    public function creating_kernal_should_bind_action_to_after_theme_setup_action()
    {
        $app = new Application();

        $kernal = new TestKernal($app);

        $this->assertTrue(has_action('after_theme_setup', [$kernal, 'bootstrap']));
    }

    /** @test */
    public function bootstrap_should_pass_bootstrappers_to_app()
    {
        $app = Mockery::mock(Application::class.'[bootstrapWith]');
        $app->shouldReceive('bootstrapWith')->with([
            LoadConfiguration::class,
            RegisterProviders::class,
            BootProviders::class,
        ])->once();

        $kernal = new TestKernal($app);
        $kernal->bootstrap();
    }
}

class TestKernal extends Kernal {}
