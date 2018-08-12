<?php

namespace Rareloop\Lumberjack\Exceptions;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface HandlerInterface
{
    public function report(Exception $e);

    public function render(ServerRequestInterface $request, Exception $e) : ResponseInterface;
}
