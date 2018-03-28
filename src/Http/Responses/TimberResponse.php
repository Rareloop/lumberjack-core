<?php

namespace Rareloop\Lumberjack\Http\Responses;

use Illuminate\Contracts\Support\Arrayable as CollectionArrayable;
use Rareloop\Lumberjack\Contracts\Arrayable;
use Rareloop\Lumberjack\Exceptions\TwigTemplateNotFoundException;
use Timber\Timber;
use Zend\Diactoros\Response\HtmlResponse;

class TimberResponse extends HtmlResponse
{
    public function __construct($twigTemplate, $context, $status = 200, array $headers = [])
    {
        $template = Timber::compile($twigTemplate, $this->flattenContextToArrays($context));

        if ($template === false) {
            throw new TwigTemplateNotFoundException($twigTemplate);
        }

        parent::__construct($template, $status, $headers);
    }

    private function flattenContextToArrays(array $context) : array
    {
        // Recursively walk the array, when we find something that implements the Arrayable interface
        // flatten it to an array. Because we're passing by reference by updating what the value of
        // $item is will mutate the original data structure passed in.
        array_walk_recursive($context, function (&$item, $key) {
            if ($item instanceof Arrayable || $item instanceof CollectionArrayable) {
                $item = $this->flattenContextToArrays($item->toArray());
            }
        });

        return $context;
    }
}
