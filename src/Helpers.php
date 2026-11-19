<?php

namespace Rareloop\Lumberjack;

use ErrorException;
use Exception;
use Rareloop\Lumberjack\Exceptions\HandlerInterface;
use Rareloop\Lumberjack\Facades\Config;
use Rareloop\Lumberjack\Facades\Router;
use Rareloop\Lumberjack\Http\Responses\RedirectResponse;
use Rareloop\Lumberjack\Http\Responses\TimberResponse;
use Rareloop\Lumberjack\Facades\Session;
use Rareloop\Lumberjack\Facades\Log;
use Throwable;

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
        return static::app()->make(TimberResponse::class, [
            'twigTemplate' => $template,
            'context' => $context,
            'status' => $statusCode,
            'headers' => $headers,
        ]);
    }

    public static function route($name, $params = [])
    {
        return Router::url($name, $params);
    }

    public static function redirect($url, $statusCode = 302, $headers = [])
    {
        return new RedirectResponse($url, $statusCode, $headers);
    }

    public static function report(Throwable $e)
    {
        $handler = static::app(HandlerInterface::class);

        if (!$e instanceof Exception) {
            $e = new ErrorException(
                $e->getMessage(),
                is_int($e->getCode()) ? $e->getCode() : 0,
                E_ERROR,
                $e->getFile(),
                $e->getLine(),
                $e
            );
        }

        return $handler->report($e);
    }

    public static function request()
    {
        return static::app('request');
    }

    public static function session($key = null, $default = null)
    {
        if (is_null($key)) {
            return static::app('session');
        }

        if (is_array($key)) {
            return Session::put($key);
        }

        return Session::get($key, $default);
    }

    public static function back()
    {
        return static::redirect(static::session()->previousUrl());
    }

    public static function logger($message = null, $context = [])
    {
        if (is_null($message)) {
            return static::app('logger');
        }

        return Log::debug($message, $context);
    }
}
