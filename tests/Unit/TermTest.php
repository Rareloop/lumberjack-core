<?php

namespace Rareloop\Lumberjack\Test\Unit;

use PHPUnit\Framework\Attributes\Test;
use Rareloop\Lumberjack\Term;
use Timber\Term as TimberTerm;
use Rareloop\Lumberjack\Test\TestCase;
use Rareloop\Lumberjack\Test\Unit\Concerns\BrainMonkeyPHPUnitIntegration;

class TermTest extends TestCase
{
    use BrainMonkeyPHPUnitIntegration;

    #[Test]
    public function it_extends_the_timber_term_class(): void
    {
        $term = TestableTerm::create();

        $this->assertInstanceOf(TimberTerm::class, $term);
        $this->assertInstanceOf(Term::class, $term);
    }

    #[Test]
    public function can_extend_term_with_macros(): void
    {
        Term::macro('testMacro', function () {
            return 'macro_result';
        });

        $term = TestableTerm::create();

        $this->assertSame('macro_result', $term->testMacro());
    }
}

class TestableTerm extends Term
{
    public function __construct() {}
    public static function create() { return new static(); }
}
