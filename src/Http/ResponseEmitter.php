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
        // We can circumvent this by cleaning the buffer and echoing the content.
        if (ob_get_level() > 0 && ob_get_length() > 0) {
            echo ob_get_clean();
        }

        (new SapiEmitter())->emit($response);
    }
}
