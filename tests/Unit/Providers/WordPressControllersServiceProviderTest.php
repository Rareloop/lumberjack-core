<?php

namespace Rareloop\Lumberjack\Test\Providers;

use \Mockery;
use Brain\Monkey\Filters;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Http\Kernal;
use Rareloop\Lumberjack\Providers\WordPressControllersServiceProvider;
use Rareloop\Lumberjack\Test\Unit\BrainMonkeyPHPUnitIntegration;
use Zend\Diactoros\Response\TextResponse;
use Zend\Diactoros\ServerRequest;

class WordPressControllersServiceProviderTest extends TestCase
{
    use BrainMonkeyPHPUnitIntegration;

    /** @test */
    public function template_include_filter_is_applied_on_boot()
    {
        $app = new Application(__DIR__.'/../');
        $provider = new WordPressControllersServiceProvider($app);

        $app->register($provider);
        $app->boot();

        $this->assertTrue(has_filter('template_include', [$provider, 'handleTemplateInclude']));
    }

    /** @test */
    public function handle_template_include_method_includes_the_requested_file()
    {
        $app = new Application(__DIR__.'/../');

        $this->assertNotContains(__DIR__ . '/includes/single.php', get_included_files());

        $provider = new WordPressControllersServiceProvider($app);
        $provider->handleTemplateInclude(__DIR__ . '/includes/single.php');

        $this->assertContains(__DIR__ . '/includes/single.php', get_included_files());
    }

    /** @test */
    public function can_get_name_of_controller_from_template()
    {
        $app = new Application(__DIR__.'/../');
        $provider = new WordPressControllersServiceProvider($app);

        $mappings = [
            'Theme\\SingleController' => __DIR__ . '/includes/single.php',
            'Theme\\SingleEventsController' => __DIR__ . '/includes/single_events.php',
            'Theme\\SingleRlEventsController' => __DIR__ . '/includes/single_rl_events.php',
        ];

        foreach ($mappings as $className => $template) {
            $this->assertSame($className, $provider->getControllerClassFromTemplate($template));
        }
    }

    /** @test */
    public function handle_request_returns_false_if_controller_does_not_exist()
    {
        $app = new Application(__DIR__.'/../');
        $provider = new WordPressControllersServiceProvider($app);

        $response = $provider->handleRequest(new ServerRequest, 'Does\\Not\\Exist', 'handle');

        $this->assertFalse($response);
    }

    /** @test */
    public function handle_request_returns_response_when_controller_does_exist()
    {
        $app = new Application(__DIR__.'/../');

        $provider = new WordPressControllersServiceProvider($app);
        $provider->boot($app);

        $response = $provider->handleRequest(new ServerRequest, TestController::class, 'handle');

        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    /** @test */
    public function handle_request_resolves_constructor_params_from_container()
    {
        $app = new Application(__DIR__.'/../');

        $provider = new WordPressControllersServiceProvider($app);
        $provider->boot($app);

        $response = $provider->handleRequest(new ServerRequest, TestControllerWithConstructorParams::class, 'handle');

        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    /** @test */
    public function handle_template_include_will_call_app_shutdown_when_it_has_handled_a_request()
    {
        $response = new TextResponse('Testing 123', 404);
        $app = Mockery::mock(Application::class.'[shutdown]', [__DIR__.'/..']);
        $app->shouldReceive('shutdown')->times(1)->with($response);

        $provider = Mockery::mock(WordPressControllersServiceProvider::class.'[handleRequest]', [$app]);
        $provider->shouldReceive('handleRequest')->once()->andReturn($response);
        $provider->boot($app);

        $provider->handleTemplateInclude(__DIR__ . '/includes/single.php');
    }

    /** @test */
    public function handle_template_include_will_not_call_app_shutdown_when_it_has_not_handled_a_request()
    {
        $app = Mockery::mock(Application::class.'[shutdown]', [__DIR__.'/..']);
        $app->shouldReceive('shutdown')->times(0);

        $provider = Mockery::mock(WordPressControllersServiceProvider::class.'[handleRequest]', [$app]);
        $provider->shouldReceive('handleRequest')->once()->andReturn(false);
        $provider->boot($app);

        $provider->handleTemplateInclude(__DIR__ . '/includes/single.php');
    }
}

class TestController
{
    public function handle()
    {

    }
}

class TestControllerWithConstructorParams
{
    public function __construct(Application $app)
    {

    }

    public function handle()
    {

    }
}
