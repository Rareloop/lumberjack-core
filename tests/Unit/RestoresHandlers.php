<?php

namespace Rareloop\Lumberjack\Test\Unit;

trait RestoresHandlers
{
    protected function restoreHandlers(): void
    {
        restore_error_handler();
        restore_exception_handler();
    }
}
