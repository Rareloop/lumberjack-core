<?php

namespace Rareloop\Lumberjack\Test;

use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;
use Rareloop\Lumberjack\Post;
use Rareloop\Lumberjack\Test\Unit\BrainMonkeyPHPUnitIntegration;

class PostTest extends TestCase
{
    use BrainMonkeyPHPUnitIntegration;

    /** @test */
    public function register_function_calls_register_post_type_when_post_type_and_config_are_provided()
    {
        Functions\expect('register_post_type')
            ->once()
            ->with(RegisterablePostType::getPostType(), RegisterablePostType::getPrivateConfig());

        RegisterablePostType::register();
    }

    /**
     * @test
     * @expectedException     Rareloop\Lumberjack\Exceptions\PostTypeRegistrationException
     */
    public function register_function_throws_exception_if_post_type_is_not_provided()
    {
        UnregisterablePostTypeWithoutPostType::register();
    }

    /**
     * @test
     * @expectedException     Rareloop\Lumberjack\Exceptions\PostTypeRegistrationException
     */
    public function register_function_throws_exception_if_config_is_not_provided()
    {
        UnregisterablePostTypeWithoutConfig::register();
    }
}

class RegisterablePostType extends Post
{
    public static function getPostType() : string
    {
        return 'registerable_post_type';
    }

    protected static function getPostTypeConfig() : array
    {
        return [
            'labels' => [
                'name' => 'Groups',
                'singular_name' => 'Group'
            ],
            'public' => true,
            'has_archive' => false,
            'supports' => ['title', 'revisions'],
            'menu_icon' => 'dashicons-groups',
            'rewrite' => [
                'slug' => 'group',
            ],
        ];
    }

    public static function getPrivateConfig()
    {
        return self::getPostTypeConfig();
    }
}

class UnregisterablePostTypeWithoutPostType extends Post
{
    protected static function getPostTypeConfig() : array
    {
        return [
            'labels' => [
                'name' => 'Groups',
                'singular_name' => 'Group'
            ],
            'public' => true,
            'has_archive' => false,
            'supports' => ['title', 'revisions'],
            'menu_icon' => 'dashicons-groups',
            'rewrite' => [
                'slug' => 'group',
            ],
        ];
    }
}

class UnregisterablePostTypeWithoutConfig extends Post
{
    public static function getPostType() : string
    {
        return 'post_type';
    }
}
