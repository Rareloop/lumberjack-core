<?php

use Rareloop\Lumberjack\Helpers;

if (!function_exists('app')) {
    function app()
    {
        return call_user_func_array([Helpers::class, 'app'], func_get_args());
    }
}

if (!function_exists('config')) {
    function config()
    {
        return call_user_func_array([Helpers::class, 'config'], func_get_args());
    }
}

if (!function_exists('view')) {
    function view()
    {
        return call_user_func_array([Helpers::class, 'view'], func_get_args());
    }
}
