<?php

namespace Rareloop\Lumberjack\Http\Responses;

use Timber\Timber;
use Zend\Diactoros\Response\HtmlResponse;

class TimberResponse extends HtmlResponse
{
    public function __construct($twigTemplate, $context, $status = 200, array $headers = [])
    {
        parent::__construct(Timber::compile($twigTemplate, $context), $status, $headers);
    }
}
