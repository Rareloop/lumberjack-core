<?php

namespace Rareloop\Lumberjack\Test;

use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Asserts that the provided callback triggers an E_USER_ERROR.
     */
    protected function assertTriggeredError(callable $callback, string $expectedMessage = ''): void
    {
        $errorTriggered = false;
        $actualMessage = '';

        set_error_handler(function (int $errno, string $errstr) use (&$errorTriggered, &$actualMessage) {
            $errorTriggered = true;
            $actualMessage = $errstr;

            throw new \ErrorException($errstr, 0, $errno);
        }, E_USER_ERROR);

        try {
            $callback();
        } catch (\ErrorException $e) {
            // Error was caught, we can proceed to assertions
        } finally {
            restore_error_handler();
        }

        // 4. Perform the actual PHPUnit assertions
        $this->assertTrue($errorTriggered, 'Failed asserting that an E_USER_ERROR was triggered.');

        if ($expectedMessage !== '') {
            $this->assertStringContainsString(
                $expectedMessage,
                $actualMessage,
                "The triggered error message did not contain: '{$expectedMessage}'"
            );
        }
    }
}
