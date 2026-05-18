<?php

namespace Rareloop\Lumberjack\Http;

use Rareloop\Lumberjack\Helpers;
use Rareloop\Lumberjack\Http\Responses\TimberResponse;
use Rareloop\Router\Controller as BaseController;

class Controller extends BaseController
{
    /**
     * Return a new TimberResponse from the controller.
     *
     * @param string $template
     * @param array|\Illuminate\Contracts\Support\Arrayable $context
     * @param integer $status
     * @param array $headers
     * @return \Rareloop\Lumberjack\Http\Responses\TimberResponse
     */
    public function view(string $template, $context = [], int $status = 200, array $headers = [])
    {
        return Helpers::view($template, $context, $status, $headers);
    }
}
