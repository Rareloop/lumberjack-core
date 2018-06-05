<?php

namespace Rareloop\Lumberjack;

use Rareloop\Lumberjack\Facades\Config;

class Helpers
{
    public static function app($key = null, $params = [])
    {
        $app = $GLOBALS['__app__'];

        if ($key === null) {
            return $app;
        }

        return $app->make($key, $params);
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
}
