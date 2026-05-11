<?php

namespace Rareloop\Lumberjack\Test;

use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Asserts that the provided callback triggers an E_USER_ERROR.
     */
    protected function assertTriggeredUserError(callable $callback, string $expectedMessage = ''): void
    {
        $errorTriggered = false;
        $actualMessage = '';

        set_error_handler(function (int $errNo, string $errStr) use (&$errorTriggered, &$actualMessage) {
            $errorTriggered = true;
            $actualMessage = $errStr;

            throw new \ErrorException($errStr, 0, $errNo);
        }, E_USER_ERROR);

        try {
            $callback();
        } catch (\ErrorException $e) {
            // Error was caught, we can proceed to assertions
        } finally {
            restore_error_handler();
        }

        $this->assertTrue($errorTriggered, 'Failed asserting that an E_USER_ERROR was triggered.');

        if ($expectedMessage !== '') {
            $this->assertEquals(
                $expectedMessage,
                $actualMessage,
                "The triggered error message was not: '{$expectedMessage}'"
            );
        }
    }
}
