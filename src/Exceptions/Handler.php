<?php

namespace Rareloop\Lumberjack\Exceptions;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Facades\Config;
use Symfony\Component\ErrorHandler\ErrorRenderer\HtmlErrorRenderer;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
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

        if ($this->app->has('logger')) {
            $logger = $this->app->get('logger');
            $logger->error($e);
        }
    }

    public function render(ServerRequestInterface $request, Exception $e) : ResponseInterface
    {
        $htmlRenderer = new HtmlErrorRenderer(Config::get('app.debug', false));

        $exception = $htmlRenderer->render($e);

        return new HtmlResponse($exception->getAsString(), $exception->getStatusCode(), $exception->getHeaders());
    }

    protected function shouldNotReport(Exception $e)
    {
        return in_array(get_class($e), $this->dontReport);
    }
}
