<?php

namespace Rareloop\Lumberjack\Exceptions;

use Exception;
use Throwable;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface HandlerInterface
{
    public function report(Throwable $e);

    public function render(ServerRequestInterface $request, Throwable $e): ResponseInterface;
}
