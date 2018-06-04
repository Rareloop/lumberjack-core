<?php

namespace Rareloop\Lumberjack;

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
}
