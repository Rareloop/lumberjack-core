<?php

namespace Rareloop\Lumberjack\Http;

use Psr\Http\Message\ServerRequestInterface;
use Rareloop\Psr7ServerRequestExtension\InteractsWithInput;
use Rareloop\Psr7ServerRequestExtension\InteractsWithUri;
use Zend\Diactoros\ServerRequest as DiactorosServerRequest;

class ServerRequest extends DiactorosServerRequest
{
    use InteractsWithInput, InteractsWithUri;

    public static function fromRequest(ServerRequestInterface $request)
    {
        $parsedBody = $request->getParsedBody();

        // Is this a JSON request?
        if (stripos($request->getHeaderLine('Content-Type'), 'application/json') !== false) {
            $parsedBody = @json_decode($request->getBody()->getContents(), true);
        }

        return new static(
            $request->getServerParams(),
            $request->getUploadedFiles(),
            $request->getUri(),
            $request->getMethod(),
            $request->getBody(),
            $request->getHeaders(),
            $request->getCookieParams(),
            $request->getQueryParams(),
            $parsedBody,
            $request->getProtocolVersion()
        );
    }

    public function ajax() : bool
    {
        if (!$this->hasHeader('X-Requested-With')) {
            return false;
        }

        return 'XMLHttpRequest' === $this->getHeader('X-Requested-With')[0];
    }

    public function getMethod() : string
    {
        return strtoupper(parent::getMethod());
    }
}
