<?php

namespace Rareloop\Lumberjack\Test;

use Brain\Monkey\Functions;
use Mockery;
use Rareloop\Lumberjack\Test\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Config;
use Rareloop\Lumberjack\Post;
use Rareloop\Lumberjack\Providers\CustomPostTypesServiceProvider;
use Rareloop\Lumberjack\Test\Unit\Concerns\BrainMonkeyPHPUnitIntegration;

class CustomPostTypesServiceProviderTest extends TestCase
{
    use BrainMonkeyPHPUnitIntegration;

    #[Test]
    public function should_call_register_post_type_for_each_configured_post_type(): void
    {
        $app = new Application(__DIR__ . '/..');

        $config = new Config();

        $config->set('posttypes.register', [
            CustomPost1::class,
            CustomPost2::class,
        ]);

        Functions\expect('register_post_type')
            ->times(2);

        $provider = new CustomPostTypesServiceProvider($app);
        $provider->boot($config);
    }

    #[Test]
    public function should_call_register_post_type_for_closures_in_configured_post_types(): void
    {
        $app = new Application(__DIR__ . '/..');

        $config = new Config();

        $config->set('posttypes.register', [
            fn() => CustomPost1::class,
            CustomPost2::class,
        ]);

        Functions\expect('register_post_type')
            ->times(2);

        $provider = new CustomPostTypesServiceProvider($app);
        $provider->boot($config);
    }

    #[Test]
    public function should_resolve_dependencies_in_closures_in_configured_post_types(): void
    {
        $app = new Application(__DIR__ . '/..');
        $app->bind(PostResolverHelper::class, new PostResolverHelper());

        $config = new Config();

        $config->set('posttypes.register', [
            fn(PostResolverHelper $helper) => $helper->resolvePostClass(),
        ]);

        Functions\expect('register_post_type')
            ->once();

        $provider = new CustomPostTypesServiceProvider($app);
        $provider->boot($config);
    }

    #[Test]
    public function should_ignore_null_and_falsy_values_in_configured_post_types(): void
    {
        $app = new Application(__DIR__ . '/..');

        $config = new Config();

        $config->set('posttypes.register', [
            null,
            fn() => null,
            CustomPost1::class,
        ]);

        Functions\expect('register_post_type')
            ->once();

        $provider = new CustomPostTypesServiceProvider($app);
        $provider->boot($config);
    }
}

class PostResolverHelper
{
    public function resolvePostClass(): string
    {
        return CustomPost1::class;
    }
}

class CustomPost1 extends Post
{
    #[\Override]
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
    #[\Override]
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
