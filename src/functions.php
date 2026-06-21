<?php

use Rareloop\Lumberjack\Helpers;

if (!function_exists('app')) {
    function app($key = null)
    {
        return call_user_func_array([Helpers::class, 'app'], func_get_args());
    }
}

if (!function_exists('config')) {
    function config($key, $default = null)
    {
        return call_user_func_array([Helpers::class, 'config'], func_get_args());
    }
}

if (!function_exists('view')) {
    function view($template, $context = [], $statusCode = 200, $headers = [])
    {
        return call_user_func_array([Helpers::class, 'view'], func_get_args());
    }
}

if (!function_exists('route')) {
    function route($name, $params = [])
    {
        return call_user_func_array([Helpers::class, 'route'], func_get_args());
    }
}

if (!function_exists('redirect')) {
    function redirect($url, $statusCode = 302, $headers = [])
    {
        return call_user_func_array([Helpers::class, 'redirect'], func_get_args());
    }
}

if (!function_exists('report')) {
    function report(Exception $e)
    {
        return call_user_func_array([Helpers::class, 'report'], func_get_args());
    }
}

if (!function_exists('session')) {
    function session($key = null, $default = null)
    {
        return call_user_func_array([Helpers::class, 'session'], func_get_args());
    }
}

if (!function_exists('back')) {
    function back()
    {
        return call_user_func_array([Helpers::class, 'back'], func_get_args());
    }
}

if (!function_exists('request')) {
    function request()
    {
        return call_user_func_array([Helpers::class, 'request'], func_get_args());
    }
}

if (!function_exists('logger')) {
    function logger($message = null, $context = [])
    {
        return call_user_func_array([Helpers::class, 'logger'], func_get_args());
    }
}
