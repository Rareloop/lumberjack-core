<?php

namespace Rareloop\Lumberjack\Http;

use Rareloop\Psr7ServerRequestExtension\InteractsWithInput;
use Rareloop\Psr7ServerRequestExtension\InteractsWithUri;
use Zend\Diactoros\ServerRequest as DiactorosServerRequest;

class ServerRequest extends DiactorosServerRequest
{
    use InteractsWithInput, InteractsWithUri;
}
