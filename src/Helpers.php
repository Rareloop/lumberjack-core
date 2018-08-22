<?php

namespace Rareloop\Lumberjack;

use Exception;
use Rareloop\Lumberjack\Exceptions\HandlerInterface;
use Rareloop\Lumberjack\Facades\Config;
use Rareloop\Lumberjack\Facades\Router;
use Rareloop\Lumberjack\Http\Responses\TimberResponse;
use Zend\Diactoros\Response\RedirectResponse;

class Helpers
{
    public static function app($key = null)
    {
        $app = $GLOBALS['__app__'];

        if ($key === null) {
            return $app;
        }

        return $app->get($key);
    }

    public static function config($key, $default = null)
    {
        if (is_array($key)) {
            $keyValues = $key;

            foreach ($keyValues as $key => $value) {
                Config::set($key, $value);
            }

            return;
        }

        return Config::get($key, $default);
    }

    public static function view($template, $context = [], $statusCode = 200, $headers = [])
    {
        return new TimberResponse($template, $context, $statusCode, $headers);
    }

    public static function route($name, $params = [])
    {
        return Router::url($name, $params);
    }

    public static function redirect($url, $statusCode = 302, $headers = [])
    {
        return new RedirectResponse($url, $statusCode, $headers);
    }

    public static function report(Exception $e)
    {
        return static::app()->get(HandlerInterface::class)->report($e);
    }
}
