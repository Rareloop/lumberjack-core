<?php

namespace Rareloop\Lumberjack\Exceptions;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Facades\Config;
use Symfony\Component\Debug\ExceptionHandler as SymfonyExceptionHandler;
use Symfony\Component\Debug\Exception\FlattenException;
use Zend\Diactoros\Response\HtmlResponse;

class Handler implements HandlerInterface
{
    protected $app;

    protected $dontReport = [];

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function report(Exception $e)
    {
        if ($this->shouldNotReport($e)) {
            return;
        }

        $logger = $this->app->get('logger');
        $logger->error($e);
    }

    public function render(ServerRequestInterface $request, Exception $e) : ResponseInterface
    {
        $e = FlattenException::create($e);

        $handler = new SymfonyExceptionHandler(Config::get('app.debug', false));

        return new HtmlResponse($handler->getHtml($e), $e->getStatusCode(), $e->getHeaders());
    }

    protected function shouldNotReport(Exception $e)
    {
        return in_array(get_class($e), $this->dontReport);
    }
}
