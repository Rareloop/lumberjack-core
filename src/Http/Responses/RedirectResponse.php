<?php

namespace Rareloop\Lumberjack\Http\Responses;

use Rareloop\Lumberjack\Helpers;
use Zend\Diactoros\Response\RedirectResponse as DiactorosRedirectResponse;

class RedirectResponse extends DiactorosRedirectResponse
{
    public function with($key, $value)
    {
        Helpers::app('session')->flash($key, $value);
    }
}
