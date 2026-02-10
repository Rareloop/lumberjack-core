<?php

namespace Rareloop\Lumberjack\Exceptions;

use Exception;
use Spatie\Ignition\Ignition;
use Rareloop\Lumberjack\Application;
use Psr\Http\Message\ResponseInterface;
use Rareloop\Lumberjack\Facades\Config;
use Laminas\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\ServerRequestInterface;
use Spatie\Ignition\Config\IgnitionConfig;

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
        $isDebug = Config::get('app.debug', false) === true;

        $ignition = Ignition::make()
            ->shouldDisplayException($isDebug)
            ->runningInProductionEnvironment(!$isDebug)
            ->register();

        $ignition->setConfig(new IgnitionConfig(Config::get('ignition', [])));

        ob_start();

        $ignition->handleException($e);

        $html = ob_get_clean();

        return new HtmlResponse($html);
    }

    protected function shouldNotReport(Exception $e)
    {
        return in_array(get_class($e), $this->dontReport);
    }
}
