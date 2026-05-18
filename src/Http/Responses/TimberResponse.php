<?php

namespace Rareloop\Lumberjack\Http\Responses;

use Illuminate\Contracts\Support\Arrayable as CollectionArrayable;
use Rareloop\Lumberjack\Contracts\Arrayable;
use Rareloop\Lumberjack\Exceptions\TwigTemplateNotFoundException;
use Timber\Timber;
use Laminas\Diactoros\Response\HtmlResponse;

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

    private function flattenContextToArrays(array|Arrayable|CollectionArrayable $context): array
    {
        $context = is_array($context) ? $context : $context->toArray();

        array_walk_recursive($context, function (&$item) {
            if ($item instanceof Arrayable || $item instanceof CollectionArrayable) {
                $item = $this->flattenContextToArrays($item);
            }
        });

        return $context;
    }
}
