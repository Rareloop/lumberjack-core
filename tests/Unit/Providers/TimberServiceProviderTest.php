<?php

namespace Rareloop\Lumberjack\Test\Providers;

use Mockery;
use Brain\Monkey;
use Timber\Timber;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;
use Rareloop\Lumberjack\Config;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Http\Lumberjack;
use Rareloop\Lumberjack\Bootstrappers\BootProviders;
use Rareloop\Lumberjack\Bootstrappers\RegisterProviders;
use Rareloop\Lumberjack\Providers\TimberServiceProvider;
use Rareloop\Lumberjack\Test\Unit\BrainMonkeyPHPUnitIntegration;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 * The above is required as we're using alias mocks which persist between tests
 * https://laracasts.com/discuss/channels/testing/mocking-a-class-persists-over-tests/replies/103075
 */
class TimberServiceProviderTest extends TestCase
{
    use BrainMonkeyPHPUnitIntegration;

    /** @test */
    public function timber_plugin_is_initialiased()
    {
        Functions\expect('is_admin')->once()->andReturn(false);

        $timber = Mockery::mock('alias:' . Timber::class);
        $timber->shouldReceive('init')->once();

        $app = new Application(__DIR__ . '/../');
        $lumberjack = new Lumberjack($app);

        $app->register(new TimberServiceProvider($app));
        $lumberjack->bootstrap();
    }

    /** @test */
    public function dirname_variable_is_set_from_config()
    {
        $app = new Application(__DIR__ . '/../');

        $config = new Config;
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
