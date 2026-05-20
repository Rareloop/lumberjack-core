<?php

namespace Rareloop\Lumberjack\Test\Unit;

use PHPUnit\Framework\Attributes\Test;
use Rareloop\Lumberjack\User;
use Rareloop\Lumberjack\Test\TestCase;
use Rareloop\Lumberjack\Test\Unit\Concerns\BrainMonkeyPHPUnitIntegration;

class UserTest extends TestCase
{
    use BrainMonkeyPHPUnitIntegration;

    #[Test]
    public function can_extend_user_with_macros(): void
    {
        User::macro('testMacro', function () {
            return 'macro_result';
        });

        $userClass = new class extends User
        {
            public function __construct()
            {
            }
        };

        $this->assertSame('macro_result', (new $userClass())->testMacro());
    }
}
