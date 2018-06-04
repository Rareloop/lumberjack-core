<?php

use Rareloop\Lumberjack\Helpers;

if (!function_exists('app')) {
    function app()
    {
        return call_user_func_array([Helpers::class, 'app'], func_get_args());
    }
}
