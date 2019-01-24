<?php

namespace Rareloop\Lumberjack\Test;

use PHPUnit\Framework\TestCase;
use Rareloop\Lumberjack\Config;

class ConfigTest extends TestCase
{
    /** @test */
    public function config_values_can_be_set_and_get()
    {
        $config = new Config;

        $config->set('app.environment', 'production');

        $this->assertSame('production', $config->get('app.environment'));
    }

    /** @test */
    public function get_returns_default_when_no_value_is_set()
    {
        $config = new Config;

        $this->assertNull($config->get('app.environment'));
        $this->assertSame('production', $config->get('app.environment', 'production'));
    }

    /** @test */
    public function get_ignores_default_when_value_is_set()
    {
        $config = new Config;

        $config->set('app.environment', 'production');

        $this->assertSame('production', $config->get('app.environment', 'staging'));
    }

    /** @test */
    public function get_returns_default_when_using_dot_notation_but_not_an_array()
    {
        $config = new Config;

        $config->set('app.logs', 'app.log');

        $this->assertSame(false, $config->get('app.logs.enabled', false));
    }

    /** @test */
    public function set_is_chainable()
    {
        $config = new Config;

        $this->assertSame($config, $config->set('app.environment', 'production'));
    }

    /** @test */
    public function can_read_config_from_files()
    {
        $config = new Config;

        $config->load(__DIR__ . '/config');

        $this->assertSame('production', $config->get('app.environment'));
        $this->assertSame(true, $config->get('app.multi.level'));
        $this->assertSame(123, $config->get('another.test'));
    }

    /** @test */
    public function can_read_config_from_files_in_constructor()
    {
        $config = new Config(__DIR__ . '/config');

        $this->assertSame('production', $config->get('app.environment'));
        $this->assertSame(true, $config->get('app.multi.level'));
        $this->assertSame(123, $config->get('another.test'));
    }

    /** @test */
    public function read_is_chainable()
    {
        $config = new Config;

        $this->assertSame($config, $config->load(__DIR__ . '/config'));
    }

    /** @test */
    public function config_values_can_be_checked_for_existence()
    {
        $config = new Config;

        $config->set('app.environment', 'production');
        $config->set('app.null', null);
        $config->set('app.false', false);

        $this->assertTrue($config->has('app.environment'));
        $this->assertTrue($config->has('app'));
        $this->assertTrue($config->has('app.false'));
        $this->assertTrue($config->has('app.null'));

        $this->assertFalse($config->has('app.nope'));
        $this->assertFalse($config->has('nope.nope'));
    }
}
