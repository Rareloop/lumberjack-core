<?php

namespace Rareloop\Lumberjack\Test;

use Brain\Monkey\Functions;
use Mockery;
use PHPUnit\Framework\TestCase;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Config;
use Rareloop\Lumberjack\Providers\MenusServiceProvider;
use Rareloop\Lumberjack\Test\Unit\BrainMonkeyPHPUnitIntegration;

class MenusServiceProviderTest extends TestCase
{
    use BrainMonkeyPHPUnitIntegration;

    /** @test */
    public function add_theme_support_should_be_called_with_menus()
    {
        $app = new Application(__DIR__ . '/..');
        $config = new Config;

        $config->set('menus.menus', []);

        Functions\expect('add_theme_support')
            ->once()
            ->with('menus');

        $provider = new MenusServiceProvider($app);
        $provider->boot($config);
    }

    /** @test */
    public function single_menu_should_be_set_from_config()
    {
        $app = new Application(__DIR__ . '/..');
        $config = new Config;

        $config->set('menus.menus', [
            [ 'menu-name' => 'Menu Name' ],
        ]);

        Functions\expect('add_theme_support')
            ->once()
            ->with('menus');

        Functions\expect('register_nav_menus')
            ->once()
            ->with([[ 'menu-name' => 'Menu Name' ]]);

        $provider = new MenusServiceProvider($app);
        $provider->boot($config);
    }

    /** @test */
    public function multiple_menus_should_be_set_from_config()
    {
        $app = new Application(__DIR__ . '/..');
        $config = new Config;

        $config->set('menus.menus', [
            [ 'menu-name' => 'Menu Name' ],
            [ 'another-menu-name' => 'Another Menu Name' ],
        ]);

        Functions\expect('add_theme_support')
            ->once()
            ->with('menus');

        Functions\expect('register_nav_menus')
            ->once()
            ->with([[ 'menu-name' => 'Menu Name' ], [ 'another-menu-name' => 'Another Menu Name' ]]);

        $provider = new MenusServiceProvider($app);
        $provider->boot($config);
    }
}
