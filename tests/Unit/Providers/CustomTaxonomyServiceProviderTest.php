<?php

namespace Rareloop\Lumberjack\Test;

use Brain\Monkey\Functions;
use Mockery;
use PHPUnit\Framework\TestCase;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Config;
use Rareloop\Lumberjack\Term;
use Rareloop\Lumberjack\Providers\CustomTaxonomyServiceProvider;
use Rareloop\Lumberjack\Test\Unit\BrainMonkeyPHPUnitIntegration;

class CustomTaxonomyServiceProviderTest extends TestCase
{
    use BrainMonkeyPHPUnitIntegration;

    /** @test */
    public function should_call_register_taxonomy_for_each_configured_taxonomy()
    {
        $app = new Application(__DIR__ . '/..');

        $config = new Config;

        $config->set('taxonomies.register', [
            CustomTaxonomy1::class,
            CustomTaxonomy2::class,
        ]);

        Functions\expect('register_taxonomy')
            ->times(2);

        $provider = new CustomTaxonomyServiceProvider($app);
        $provider->boot($config);
    }
}

class CustomTaxonomy1 extends Term
{
    public static function getTaxonomyType()
    {
        return 'custom_taxonomy_1';
    }

    public static function getTaxonomyObjectTypes()
    {
        return ['post'];
    }

    protected static function getTaxonomyConfig()
    {
        return [
            'not' => 'empty',
        ];
    }
}

class CustomTaxonomy2 extends Term
{
    public static function getTaxonomyType()
    {
        return 'custom_taxonomy_1';
    }

    public static function getTaxonomyObjectTypes()
    {
        return ['post'];
    }

    protected static function getTaxonomyConfig()
    {
        return [
            'not' => 'empty',
        ];
    }
}
