<?php

namespace Rareloop\Lumberjack\Test\Bootstrappers;

use Mockery;
use PHPUnit\Framework\TestCase;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Bootstrappers\RegisterRequestHandler;
use Rareloop\Lumberjack\Config;

class RegisterRequestHandlerTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    /** @test */
    public function calls_function_on_app_when_in_debug_mode()
    {
        $app = Mockery::mock(Application::class . '[detectWhenRequestHasNotBeenHandled]');
        $app->shouldReceive('detectWhenRequestHasNotBeenHandled')->once();

        $config = new Config;
        $config->set('app.debug', true);
        $app->bind('config', $config);

        $bootstrapper = new RegisterRequestHandler;
        $bootstrapper->bootstrap($app);
    }

    /** @test */
    public function does_not_call_function_on_app_when_not_in_debug_mode()
    {
        $app = Mockery::mock(Application::class . '[detectWhenRequestHasNotBeenHandled]');
        $app->shouldNotReceive('detectWhenRequestHasNotBeenHandled');

        $config = new Config;
        $config->set('app.debug', false);
        $app->bind('config', $config);

        $bootstrapper = new RegisterRequestHandler;
        $bootstrapper->bootstrap($app);
    }
}
