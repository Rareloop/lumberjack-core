<?php

namespace Rareloop\Lumberjack\Http\Responses;

use Rareloop\Lumberjack\Exceptions\TwigTemplateNotFoundException;
use Timber\Timber;
use Zend\Diactoros\Response\HtmlResponse;

class TimberResponse extends HtmlResponse
{
    public function __construct($twigTemplate, $context, $status = 200, array $headers = [])
    {
        $template = Timber::compile($twigTemplate, $context);

        if ($template === false) {
            throw new TwigTemplateNotFoundException($twigTemplate);
        }

        parent::__construct($template, $status, $headers);
    }
}
