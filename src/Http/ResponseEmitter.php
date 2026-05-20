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

        // If the output buffer has content, SapiEmitter will throw an exception.
        // We circumvent this by stacking a new buffer, emitting into it,
        // and then echoing the result.
        if (ob_get_level() > 0 && ob_get_length() > 0) {
            ob_start();
            (new SapiEmitter())->emit($response);
            echo ob_get_clean();
            return;
        }

        (new SapiEmitter())->emit($response);
    }
}
