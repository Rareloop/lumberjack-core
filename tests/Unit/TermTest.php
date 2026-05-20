<?php

namespace Rareloop\Lumberjack\Test\Unit;

use PHPUnit\Framework\Attributes\Test;
use Rareloop\Lumberjack\Term;
use Rareloop\Lumberjack\Test\TestCase;
use Rareloop\Lumberjack\Test\Unit\Concerns\BrainMonkeyPHPUnitIntegration;

class TermTest extends TestCase
{
    use BrainMonkeyPHPUnitIntegration;

    #[Test]
    public function can_extend_term_with_macros(): void
    {
        Term::macro('testMacro', function () {
            return 'macro_result';
        });

        $termClass = new class extends Term
        {
            public function __construct()
            {
            }
        };

        $this->assertSame('macro_result', (new $termClass())->testMacro());
    }
}
