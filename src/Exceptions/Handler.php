<?php

namespace Rareloop\Lumberjack\Exceptions;

use Exception;
use Spatie\Ignition\Ignition;
use Rareloop\Lumberjack\Application;
use Psr\Http\Message\ResponseInterface;
use Rareloop\Lumberjack\Facades\Config;
use Laminas\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

use Rareloop\Lumberjack\Http\Responses\TimberResponse;

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

    public function render(ServerRequestInterface $request, Exception $e): ResponseInterface
    {
        if (Config::get('app.debug', false) === true) {
            return $this->renderExceptionWithIgnition($e);
        }

        return $this->renderDefaultErrorView($e);
    }

    protected function renderExceptionWithIgnition(Exception $e): ResponseInterface
    {
        $ignition = $this->app->get(Ignition::class);

        ob_start();
        $ignition->handleException($e);

        return new HtmlResponse(ob_get_clean());
    }

    protected function renderDefaultErrorView(Exception $e): ResponseInterface
    {
        $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;

        try {
            return new TimberResponse(__DIR__ . '/views/error.twig', [
                'status_code' => $status,
            ], $status);
        } catch (Throwable) {
            return new HtmlResponse("Lumberjack | {$status}", $status);
        }
    }

    protected function shouldNotReport(Exception $e)
    {
        return in_array($e::class, $this->dontReport);
    }
}
