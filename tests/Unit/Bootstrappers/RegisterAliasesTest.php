<?php

namespace Rareloop\Lumberjack\Test\Bootstrappers;

use Mockery;
use Rareloop\Lumberjack\Test\TestCase;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Bootstrappers\RegisterAliases;
use Rareloop\Lumberjack\Config;
use PHPUnit\Framework\Attributes\Test;

class RegisterAliasesTest extends TestCase
{
    #[Test]
    public function calls_class_alias_on_all_alias_mappings()
    {
        $app = new Application();
        $config = new Config();
        $config->set('app.aliases', [
            'Foo' => TestClassToAlias::class,
        ]);
        $app->bind('config', $config);

        $bootstrapper = new RegisterAliases();
        $bootstrapper->bootstrap($app);

        $this->assertTrue(class_exists('Foo'));
        $this->assertInstanceOf(TestClassToAlias::class, new \Foo());
    }

    #[Test]
    public function calls_class_alias_on_all_alias_mappings_with_closures()
    {
        $app = new Application();
        $config = new Config();
        $config->set('app.aliases', [
            'Bar' => fn() => TestClassToAlias::class,
        ]);
        $app->bind('config', $config);

        $bootstrapper = new RegisterAliases();
        $bootstrapper->bootstrap($app);

        $this->assertTrue(class_exists('Bar'));
        $this->assertInstanceOf(TestClassToAlias::class, new \Bar());
    }

    #[Test]
    public function ignores_null_aliases_and_closures_returning_null()
    {
        $app = new Application();
        $config = new Config();
        $config->set('app.aliases', [
            'NullAlias' => null,
            'NullClosureAlias' => fn() => null,
            'Baz' => TestClassToAlias::class,
        ]);
        $app->bind('config', $config);

        $bootstrapper = new RegisterAliases();
        $bootstrapper->bootstrap($app);

        $this->assertFalse(class_exists('NullAlias'));
        $this->assertFalse(class_exists('NullClosureAlias'));
        $this->assertTrue(class_exists('Baz'));
        $this->assertInstanceOf(TestClassToAlias::class, new \Baz());
    }
}

class TestClassToAlias
{
}
