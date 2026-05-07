<?php

namespace Rareloop\Lumberjack\Test\Bootstrappers;

use Mockery;
use PHPUnit\Framework\TestCase;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Bootstrappers\RegisterAliases;
use Rareloop\Lumberjack\Config;
use Rareloop\Lumberjack\Autodiscovery\AutodiscoveredPackages;

class RegisterAliasesTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    /** @test */
    public function calls_class_alias_on_all_alias_mappings()
    {
        $app = new Application;

        $manifest = Mockery::mock(AutodiscoveredPackages::class);
        $manifest->shouldReceive('aliases')->andReturn([]);
        $app->bind(AutodiscoveredPackages::class, $manifest);

        $config = new Config;
        $config->set('app.aliases', [
            'FooOne' => TestClassToAlias::class,
        ]);
        $app->bind('config', $config);

        $bootstrapper = new RegisterAliases;
        $bootstrapper->bootstrap($app);

        $this->assertTrue(class_exists('FooOne'));
        $this->assertInstanceOf(TestClassToAlias::class, new \FooOne);
    }

    /** @test */
    public function user_defined_aliases_take_precedence_over_autodiscovered_ones()
    {
        $app = new Application;

        $manifest = Mockery::mock(AutodiscoveredPackages::class);
        $manifest->shouldReceive('aliases')->andReturn([
            'FooTwo' => 'Package\Class\Foo',
        ]);
        $app->bind(AutodiscoveredPackages::class, $manifest);

        $config = new Config;
        $config->set('app.aliases', [
            'FooTwo' => TestClassToAlias::class,
        ]);
        $app->bind('config', $config);

        $bootstrapper = new RegisterAliases;
        $bootstrapper->bootstrap($app);

        $this->assertTrue(class_exists('FooTwo'));
        $this->assertInstanceOf(TestClassToAlias::class, new \FooTwo);
    }
}

class TestClassToAlias
{

}
