<?php

namespace Rareloop\Lumberjack\Test;

use Brain\Monkey\Functions;
use Mockery;
use PHPUnit\Framework\TestCase;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Config;
use Rareloop\Lumberjack\Post;
use Rareloop\Lumberjack\Providers\CustomPostTypesServiceProvider;
use Rareloop\Lumberjack\Test\Unit\BrainMonkeyPHPUnitIntegration;

class CustomPostTypesServiceProviderTest extends TestCase
{
    use BrainMonkeyPHPUnitIntegration;

    /** @test */
    public function should_call_register_post_type_for_each_configured_post_type()
    {
        $app = new Application(__DIR__ . '/..');

        $config = new Config;

        $config->set('posttypes.register', [
            CustomPost1::class,
            CustomPost2::class,
        ]);

        Functions\expect('register_post_type')
            ->times(2);

        $provider = new CustomPostTypesServiceProvider($app);
        $provider->boot($config);
    }
}

class CustomPost1 extends Post
{
    public static function getPostType()
    {
        return 'custom_post_1';
    }

    protected static function getPostTypeConfig()
    {
        return [
            'not' => 'empty',
        ];
    }
}

class CustomPost2 extends Post
{
    public static function getPostType()
    {
        return 'custom_post_1';
    }

    protected static function getPostTypeConfig()
    {
        return [
            'not' => 'empty',
        ];
    }
}
