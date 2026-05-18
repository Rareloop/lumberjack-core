<?php

namespace Rareloop\Lumberjack\Test\Providers;

use Mockery;
use Brain\Monkey;
use Timber\Timber;
use Brain\Monkey\Functions;
use Rareloop\Lumberjack\Test\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Rareloop\Lumberjack\Config;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Http\Lumberjack;
use Rareloop\Lumberjack\Bootstrappers\BootProviders;
use Rareloop\Lumberjack\Bootstrappers\RegisterProviders;
use Rareloop\Lumberjack\Providers\TimberServiceProvider;
use Rareloop\Lumberjack\Test\Unit\Concerns\BrainMonkeyPHPUnitIntegration;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use Rareloop\Lumberjack\Http\TimberContext;
use Rareloop\Lumberjack\Http\Responses\TimberResponse;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class TimberServiceProviderTest extends TestCase
{
    use BrainMonkeyPHPUnitIntegration;

    #[Test]
    public function it_registers_timber_context_as_a_singleton(): void
    {
        $timber = Mockery::mock('alias:' . Timber::class);
        $timber->shouldReceive('init');
        $timber->shouldReceive('context')->once()->andReturn(['foo' => 'bar']);

        $app = new Application();
        $provider = new TimberServiceProvider($app);
        $provider->register();

        $this->assertTrue($app->has(TimberContext::class));
        $context = $app->get(TimberContext::class);
        $this->assertInstanceOf(TimberContext::class, $context);
        $this->assertSame('bar', $context->get('foo'));

        // Verify singleton
        $this->assertSame($context, $app->get(TimberContext::class));
    }

    #[Test]
    public function it_binds_timber_response(): void
    {
        $timber = Mockery::mock('alias:' . Timber::class);
        $timber->shouldReceive('init');

        $app = new Application();
        $provider = new TimberServiceProvider($app);
        $provider->register();

        $this->assertTrue($app->has(TimberResponse::class));
    }

    #[Test]
    public function timber_plugin_is_initialiased(): void
    {
        Functions\expect('is_admin')->once()->andReturn(false);

        $timber = Mockery::mock('alias:' . Timber::class);
        $timber->shouldReceive('init')->once();

        $app = new Application(__DIR__ . '/../');
        $lumberjack = new Lumberjack($app);

        $app->register(new TimberServiceProvider($app));
        $lumberjack->bootstrap();
    }

    #[Test]
    public function dirname_variable_is_set_from_config(): void
    {
        $app = new Application(__DIR__ . '/../');

        $config = new Config();
        $config->set('timber.paths', [
            'path/one',
            'path/two',
            'path/three',
        ]);

        $app->bind('config', $config);
        $app->bind(Config::class, $config);

        $app->bootstrapWith([
            RegisterProviders::class,
            BootProviders::class,
        ]);

        $app->register(new TimberServiceProvider($app));

        $this->assertSame([
            'path/one',
            'path/two',
            'path/three',
        ], Timber::$dirname);
    }
}
