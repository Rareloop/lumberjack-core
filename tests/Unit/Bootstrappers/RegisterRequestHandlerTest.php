<?php

namespace Rareloop\Lumberjack\Test\Bootstrappers;

use Mockery;
use Rareloop\Lumberjack\Test\TestCase;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Bootstrappers\RegisterRequestHandler;
use Rareloop\Lumberjack\Config;
use PHPUnit\Framework\Attributes\Test;

class RegisterRequestHandlerTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    #[Test]
    public function calls_function_on_app_when_in_debug_mode()
    {
        $app = Mockery::mock(Application::class . '[detectWhenRequestHasNotBeenHandled]');
        $app->shouldReceive('detectWhenRequestHasNotBeenHandled')->once();

        $config = new Config();
        $config->set('app.debug', true);
        $app->bind('config', $config);

        $bootstrapper = new RegisterRequestHandler();
        $bootstrapper->bootstrap($app);
    }

    #[Test]
    public function does_not_call_function_on_app_when_not_in_debug_mode()
    {
        $app = Mockery::mock(Application::class . '[detectWhenRequestHasNotBeenHandled]');
        $app->shouldNotReceive('detectWhenRequestHasNotBeenHandled');

        $config = new Config();
        $config->set('app.debug', false);
        $app->bind('config', $config);

        $bootstrapper = new RegisterRequestHandler();
        $bootstrapper->bootstrap($app);
    }

    #[Test]
    public function it_binds_wp_query_to_the_global_variable()
    {
        $app = new Application();
        $config = new Config();
        $app->bind('config', $config);

        $query = new \stdClass();
        $GLOBALS['wp_query'] = $query;

        $bootstrapper = new RegisterRequestHandler();
        $bootstrapper->bootstrap($app);

        $this->assertSame($query, $app->get(\WP_Query::class));
    }
}
