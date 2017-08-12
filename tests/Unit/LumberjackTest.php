<?php

namespace Rareloop\Lumberjack\Test;

use Brain\Monkey;
use Brain\Monkey\Actions;
use Mockery;
use PHPUnit\Framework\TestCase;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Bootstrappers\BootProviders;
use Rareloop\Lumberjack\Bootstrappers\LoadConfiguration;
use Rareloop\Lumberjack\Bootstrappers\RegisterFacades;
use Rareloop\Lumberjack\Bootstrappers\RegisterProviders;
use Rareloop\Lumberjack\Http\Lumberjack;
use Rareloop\Lumberjack\Test\Unit\BrainMonkeyPHPUnitIntegration;

class LumberjackTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    /** @test */
    public function bootstrap_should_pass_bootstrappers_to_app()
    {
        $app = Mockery::mock(Application::class.'[bootstrapWith]');
        $app->shouldReceive('bootstrapWith')->with([
            LoadConfiguration::class,
            RegisterFacades::class,
            RegisterProviders::class,
            BootProviders::class,
        ])->once();

        $kernal = new Lumberjack($app);
        $kernal->bootstrap();
    }
}
