<?php

namespace Rareloop\Lumberjack\Test\Bootstrappers;

use Mockery;
use PHPUnit\Framework\TestCase;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Bootstrappers\RegisterAliases;
use Rareloop\Lumberjack\Config;

class RegisterAliasesTest extends TestCase
{
    /** @test */
    public function calls_class_alias_on_all_alias_mappings()
    {
        $app = new Application;
        $config = new Config;
        $config->set('app.aliases', [
            'Foo' => TestClassToAlias::class,
        ]);
        $app->bind('config', $config);

        $bootstrapper = new RegisterAliases;
        $bootstrapper->bootstrap($app);

        $this->assertTrue(class_exists('Foo'));
        $this->assertInstanceOf(TestClassToAlias::class, new \Foo);
    }
}

class TestClassToAlias
{

}
