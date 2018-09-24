<?php

namespace Rareloop\Lumberjack\Http\Responses;

use Rareloop\Lumberjack\Helpers;
use Zend\Diactoros\Response\RedirectResponse as DiactorosRedirectResponse;

class RedirectResponse extends DiactorosRedirectResponse
{
    public function with($key = null, $value = null)
    {
        if (is_array($key)) {
            Helpers::app('session')->flash($key);
        } else {
            Helpers::app('session')->flash($key, $value);
        }

        return $this;
    }
}
