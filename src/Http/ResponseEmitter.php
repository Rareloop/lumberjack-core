<?php

namespace Rareloop\Lumberjack\Http;

use Psr\Http\Message\ResponseInterface;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;

class ResponseEmitter
{
    /**
     * Emit a response.
     *
     * If headers have already been sent, the response body is echoed directly to avoid
     * an EmitterException from the underlying SapiEmitter.
     *
     * @param ResponseInterface $response
     * @return void
     */
    public function emit(ResponseInterface $response)
    {
        if (headers_sent()) {
            echo (string)$response->getBody();
            return;
        }

        (new SapiEmitter())->emit($response);
    }
}
