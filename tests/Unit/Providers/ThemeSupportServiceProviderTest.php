<?php

namespace Rareloop\Lumberjack\Test;

use Brain\Monkey\Functions;
use Mockery;
use PHPUnit\Framework\TestCase;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Config;
use Rareloop\Lumberjack\Providers\ThemeSupportServiceProvider;
use Rareloop\Lumberjack\Test\Unit\BrainMonkeyPHPUnitIntegration;

class ThemeSupportServiceProviderTest extends TestCase
{
    use BrainMonkeyPHPUnitIntegration;

    /** @test */
    public function should_call_add_theme_support_for_key_in_config()
    {
        $app = new Application(__DIR__ . '/..');

        $config = new Config;

        $config->set('app.themeSupport', [
            'post-thumbnail',
        ]);

        Functions\expect('add_theme_support')
            ->with('post-thumbnail')
            ->once();

        $provider = new ThemeSupportServiceProvider($app);
        $provider->boot($config);
    }

    /** @test */
    public function should_call_add_theme_support_for_key_value_in_config()
    {
        $app = new Application(__DIR__ . '/..');

        $config = new Config;

        $config->set('app.themeSupport', [
            'post-formats' => ['aside', 'gallery'],
        ]);

        Functions\expect('add_theme_support')
            ->with('post-formats', ['aside', 'gallery'])
            ->once();

        $provider = new ThemeSupportServiceProvider($app);
        $provider->boot($config);
    }
}
