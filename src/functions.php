<?php

use Rareloop\Lumberjack\Helpers;

if (!function_exists('app')) {
    function app()
    {
        return call_user_func_array(Helpers::app(...), func_get_args());
    }
}

if (!function_exists('config')) {
    function config()
    {
        return call_user_func_array(Helpers::config(...), func_get_args());
    }
}

if (!function_exists('view')) {
    function view()
    {
        return call_user_func_array(Helpers::view(...), func_get_args());
    }
}

if (!function_exists('route')) {
    function route()
    {
        return call_user_func_array(Helpers::route(...), func_get_args());
    }
}

if (!function_exists('redirect')) {
    function redirect()
    {
        return call_user_func_array(Helpers::redirect(...), func_get_args());
    }
}

if (!function_exists('report')) {
    function report()
    {
        return call_user_func_array(Helpers::report(...), func_get_args());
    }
}

if (!function_exists('session')) {
    function session()
    {
        return call_user_func_array(Helpers::session(...), func_get_args());
    }
}

if (!function_exists('back')) {
    function back()
    {
        return call_user_func_array(Helpers::back(...), func_get_args());
    }
}

if (!function_exists('request')) {
    function request()
    {
        return call_user_func_array(Helpers::request(...), func_get_args());
    }
}

if (!function_exists('logger')) {
    function logger()
    {
        return call_user_func_array(Helpers::logger(...), func_get_args());
    }
}
