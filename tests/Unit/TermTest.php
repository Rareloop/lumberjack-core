<?php

namespace Rareloop\Lumberjack\Test;

use Brain\Monkey\Functions;
use Illuminate\Support\Collection;
use Mockery;
use PHPUnit\Framework\TestCase;
use Rareloop\Lumberjack\Term;
use Rareloop\Lumberjack\Test\Unit\BrainMonkeyPHPUnitIntegration;
use Timber\Timber;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 * The above is required as we're using alias mocks which persist between tests
 * https://laracasts.com/discuss/channels/testing/mocking-a-class-persists-over-tests/replies/103075
 */
class TermTest extends TestCase
{
    use BrainMonkeyPHPUnitIntegration;

    /** @test */
    public function register_function_calls_register_taxonomy_when_taxonomy_type_and_config_are_provided()
    {
        Functions\expect('register_taxonomy')
            ->once()
            ->with(RegisterableTaxonomyType::getTaxonomyType(), RegisterableTaxonomyType::getTaxonomyObjectTypes(), RegisterableTaxonomyType::getPrivateConfig());

        RegisterableTaxonomyType::register();
    }

    /**
     * @test
     * @expectedException     Rareloop\Lumberjack\Exceptions\TaxonomyRegistrationException
     */
    public function register_function_throws_exception_if_taxonomy_type_is_not_provided()
    {
        UnregisterableTaxonomyWithoutTaxonomyType::register();
    }

    /**
     * @test
     * @expectedException     Rareloop\Lumberjack\Exceptions\TaxonomyRegistrationException
     */
    public function register_function_throws_exception_if_config_is_not_provided()
    {
        UnregisterableTaxonomyWithoutConfig::register();
    }

    /**
     * @test
     */
    public function query_defaults_to_current_taxonomy_type()
    {
        $args = [
            'show_admin_column' => true,
        ];
        $maybe_args = [];

        $timber = Mockery::mock('alias:' . Timber::class);
        $timber->shouldReceive('get_terms')->withArgs([
            array_merge($args, [
                'taxonomy' => Term::getTaxonomyType(),
            ]),
            $maybe_args,
            Term::class,
        ])->once();

        $terms = Term::query($args);

        $this->assertInstanceOf(Collection::class, $terms);
    }

    /**
     * @test
     */
    public function query_ignores_passed_in_taxonomy()
    {
        $args = [
            'taxonomy' => 'something-else',
            'show_admin_column' => true,
        ];
        $maybe_args = [];

        $timber = Mockery::mock('alias:' . Timber::class);
        $timber->shouldReceive('get_terms')->withArgs([
            array_merge($args, [
                'taxonomy' => Term::getTaxonomyType(),
                'show_admin_column' => true,
            ]),
            $maybe_args,
            Term::class,
        ])->once();

        $terms = Term::query($args);

        $this->assertInstanceOf(Collection::class, $terms);
    }

    /**
     * @test
     */
    public function term_subclass_query_has_correct_taxonomy_type()
    {
        $args = [
            'show_admin_column' => true,
        ];
        $maybe_args = [];

        $timber = Mockery::mock('alias:' . Timber::class);
        $timber->shouldReceive('get_terms')->withArgs([
            Mockery::subset([
                'taxonomy' => RegisterableTaxonomyType::getTaxonomyType(),
            ]),
            $maybe_args,
            RegisterableTaxonomyType::class,
        ])->once();

        $terms = RegisterableTaxonomyType::query($args);

        $this->assertInstanceOf(Collection::class, $terms);
    }

    /**
     * @test
     */
    public function all_defaults_to_ordered_by_term_order_ascending()
    {
        $maybe_args = [];
        $timber = Mockery::mock('alias:' . Timber::class);
        $timber->shouldReceive('get_terms')->withArgs([
            Mockery::subset([
                'orderby' => 'term_order',
                'order' => 'ASC',
            ]),
            $maybe_args,
            Term::class,
        ])->once();

        $terms = Term::all();

        $this->assertInstanceOf(Collection::class, $terms);
    }


    /**
     * @test
     */
    public function all_can_have_order_set()
    {
        $maybe_args = [];
        $timber = Mockery::mock('alias:' . Timber::class);
        $timber->shouldReceive('get_terms')->withArgs([
            Mockery::subset([
                'orderby' => 'slug',
                'order' => 'DESC',
            ]),
            $maybe_args,
            Term::class,
        ])->once();

        $terms = Term::all('slug', 'DESC');

        $this->assertInstanceOf(Collection::class, $terms);
    }

    /**
     * @test
     */
    public function can_extend_term_behaviour_with_macros()
    {
        Term::macro('testFunctionAddedByMacro', function () {
            return 'abc123';
        });

        $term = new Term(false, '', true);

        $this->assertSame('abc123', $term->testFunctionAddedByMacro());
        $this->assertSame('abc123', Term::testFunctionAddedByMacro());
    }

    /**
     * @test
     */
    public function macros_set_correct_this_context_on_instances()
    {
        Term::macro('testFunctionAddedByMacro', function () {
            return $this->dummyData();
        });

        $term = new Term(false, '', true);
        $term->dummyData = 'abc123';

        $this->assertSame('abc123', $term->testFunctionAddedByMacro());
    }

    /**
     * @test
     */
    public function can_extend_term_behaviour_with_mixin()
    {
        Term::mixin(new TermMixin);

        $term = new Term(false, '', true);

        $this->assertSame('abc123', $term->testFunctionAddedByMixin());
    }
}

class TermMixin
{
    function testFunctionAddedByMixin()
    {
	return function() {
	    return 'abc123';
	};
    }
}

class RegisterableTaxonomyType extends Term
{
    public static function getTaxonomyType() : string
    {
        return 'registerable_taxonomy_type';
    }

    public static function getTaxonomyObjectTypes() : array
    {
        return ['post'];
    }

    protected static function getTaxonomyConfig() : array
    {
        return [
            'hierarchical' => true,
            'labels' => [
                'name' => 'Tags',
                'singular_name' => 'Tag'
            ],
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => [
                'slug' => 'the-tags'
            ],
        ];
    }

    public static function getPrivateConfig()
    {
        return self::getTaxonomyConfig();
    }
}

class UnregisterableTaxonomyWithoutTaxonomyType extends Term
{
    protected static function getTaxonomyConfig() : array
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

class UnregisterableTaxonomyWithoutConfig extends Term
{
    public static function getTaxonomyType() : string
    {
        return 'taxonomy_type';
    }
}
