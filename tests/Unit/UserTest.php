<?php

namespace Rareloop\Lumberjack\Test\Unit;

use PHPUnit\Framework\Attributes\Test;
use Rareloop\Lumberjack\User;
use Timber\User as TimberUser;
use Rareloop\Lumberjack\Test\TestCase;
use Rareloop\Lumberjack\Test\Unit\Concerns\BrainMonkeyPHPUnitIntegration;

class UserTest extends TestCase
{
    use BrainMonkeyPHPUnitIntegration;

    #[Test]
    public function it_extends_the_timber_user_class(): void
    {
        $user = TestableUser::create();

        $this->assertInstanceOf(TimberUser::class, $user);
        $this->assertInstanceOf(User::class, $user);
    }

    #[Test]
    public function can_extend_user_with_macros(): void
    {
        User::macro('testMacro', function () {
            return 'macro_result';
        });

        $user = TestableUser::create();

        $this->assertSame('macro_result', $user->testMacro());
    }
}

class TestableUser extends User
{
    public function __construct() {}
    public static function create() { return new static(); }
}
