<?php

namespace Rareloop\Lumberjack\Test\Unit\Http\Resolvers;

use Brain\Monkey\Functions;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Providers\WordPressControllersServiceProvider;
use Rareloop\Lumberjack\Test\TestCase;
use Rareloop\Lumberjack\Test\Unit\Concerns\BrainMonkeyPHPUnitIntegration;
use Rareloop\Lumberjack\Test\Unit\Http\Resolvers\Stubs\TermStub;
use Rareloop\Router\Invoker;
use Timber\Term;
use Timber\Timber;
use WP_Term;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class TermResolverTest extends TestCase
{
    use BrainMonkeyPHPUnitIntegration;

    private Application $app;
    private Invoker $invoker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app = new Application(__DIR__ . '/../../../../');

        $config = Mockery::mock(\Rareloop\Lumberjack\Config::class);
        $config->shouldReceive('get')->with('app.resolvers', [])->andReturn([]);
        $this->app->bind('config', $config);

        $provider = new WordPressControllersServiceProvider($this->app);
        $provider->register();

        $this->invoker = $this->app->make(Invoker::class);
    }

    #[Test]
    public function it_can_resolve_a_timber_term(): void
    {
        $wpTerm = Mockery::mock(WP_Term::class);
        Functions\expect('get_queried_object')->once()->andReturn($wpTerm);

        $timberTerm = Mockery::mock(Term::class);
        $timber = Mockery::mock('alias:' . Timber::class);
        $timber->shouldReceive('get_term')->once()->with($wpTerm)->andReturn($timberTerm);

        $controller = new class {
            public function handle(Term $term)
            {
                return $term;
            }
        };

        $result = $this->invoker->call([$controller, 'handle']);

        $this->assertSame($timberTerm, $result);
    }

    #[Test]
    public function it_can_resolve_a_subclass_of_timber_term(): void
    {
        $wpTerm = Mockery::mock(WP_Term::class);
        Functions\expect('get_queried_object')->once()->andReturn($wpTerm);

        $termStub = Mockery::mock(TermStub::class);
        $timber = Mockery::mock('alias:' . Timber::class);
        $timber->shouldReceive('get_term')->once()->with($wpTerm)->andReturn($termStub);

        $controller = new class {
            public function handle(TermStub $term)
            {
                return $term;
            }
        };

        $result = $this->invoker->call([$controller, 'handle']);

        $this->assertSame($termStub, $result);
    }
}
