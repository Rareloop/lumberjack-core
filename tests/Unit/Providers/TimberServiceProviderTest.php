<?php

namespace Rareloop\Lumberjack\Test\Providers;

use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Bootstrappers\BootProviders;
use Rareloop\Lumberjack\Bootstrappers\RegisterProviders;
use Rareloop\Lumberjack\Config;
use Rareloop\Lumberjack\Http\Lumberjack;
use Rareloop\Lumberjack\Providers\TimberServiceProvider;
use Rareloop\Lumberjack\Test\Unit\BrainMonkeyPHPUnitIntegration;
use Timber\Timber;

class TimberServiceProviderTest extends TestCase
{
    use BrainMonkeyPHPUnitIntegration;

    /** @test */
    public function timber_plugin_is_initialiased()
    {
        Functions\expect('is_admin')->once()->andReturn(false);

        $app = new Application(__DIR__.'/../');
        $lumberjack = new Lumberjack($app);

        $app->register(new TimberServiceProvider($app));
        $lumberjack->bootstrap();

        $this->assertTrue($app->has('timber'));
        $this->assertSame($app->get('timber'), $app->get(Timber::class));
    }

    /** @test */
    public function dirname_variable_is_set_from_config()
    {
        $app = new Application(__DIR__.'/../');

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

        $this->assertTrue($app->has('timber'));
        $this->assertSame([
            'path/one',
            'path/two',
            'path/three',
        ], $app->get('timber')::$dirname);
    }
}
