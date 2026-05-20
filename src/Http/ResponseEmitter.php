<?php

namespace Rareloop\Lumberjack\Http;

use Psr\Http\Message\ResponseInterface;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Laminas\HttpHandlerRunner\Emitter\EmitterInterface;

class ResponseEmitter
{
    public function __construct(protected ?EmitterInterface $emitter = null)
    {
        $this->emitter = $emitter ?? new SapiEmitter();
    }

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
        if ($this->isOutputBufferDirty()) {
            $this->emitWithStackedBuffer($response);
            return;
        }

        $this->emitter->emit($response);
    }

    /**
     * Check if the output buffer has existing content.
     */
    protected function isOutputBufferDirty(): bool
    {
        return ob_get_level() > 0 && ob_get_length() > 0;
    }

    /**
     * Emit the response into a clean buffer to bypass SapiEmitter's strict checks.
     */
    protected function emitWithStackedBuffer(ResponseInterface $response): void
    {
        ob_start();

        try {
            $this->emitter->emit($response);
        } finally {
            echo ob_get_clean();
        }
    }
}
