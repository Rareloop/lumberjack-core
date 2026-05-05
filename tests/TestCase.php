<?php

namespace Rareloop\Lumberjack\Test;

use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected $originalErrorHandler;
    protected $originalExceptionHandler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalExceptionHandler = get_exception_handler();
        $this->originalErrorHandler = get_error_handler();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        if (get_error_handler() !== $this->originalErrorHandler) {
            restore_error_handler();
        }

        if (get_exception_handler() !== $this->originalExceptionHandler) {
            restore_exception_handler();
        }
    }

    /**
     * In PHPUnit 10+ E_USER_ERROR is no longer automatically converted to an exception.
     * This helper allows us to opt-in to this behavior for specific tests so we can
     * use $this->expectException(ErrorException::class).
     */
    protected function expectUserError(): void
    {
        set_error_handler(function ($errno, $errstr, $errfile, $errline) {
            throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
        }, E_USER_ERROR);
    }
}
