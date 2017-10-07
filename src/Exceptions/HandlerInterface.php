<?php

namespace Rareloop\Lumberjack\Exceptions;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\ServerRequest;

interface HandlerInterface
{
    public function report(Exception $e);

    public function render(ServerRequest $request, Exception $e) : ResponseInterface;
}
