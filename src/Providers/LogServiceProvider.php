<?php

namespace Rareloop\Lumberjack\Providers;

use Monolog\Handler\BufferHandler;
use Monolog\Handler\HandlerWrapper;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Rareloop\Lumberjack\Application;

class LogServiceProvider extends ServiceProvider
{
    public function register()
    {
        $logger = new Logger('app');
        $logger->pushHandler(new StreamHandler($this->getLogsPath(), $this->getLogLevel()));
        $handler = $logger->getHandlers()[0];

        if ($this->app->has('config') && !$this->app->get('config')->get('app.logs.enabled', false)) {
            $logger->popHandler();
            $logger->pushHandler(new StreamHandler('php://memory', $this->getLogLevel()));
        }

        $this->app->bind('logger', $logger);
        $this->app->bind(Logger::class, $logger);
    }

    private function getLogLevel()
    {
        $logLevel = Logger::DEBUG;

        if ($this->app->has('config')) {
            $logLevel = $this->app->get('config')->get('app.logs.level', $logLevel);
        }

        return $logLevel;
    }

    private function getLogsPath()
    {
        $logsPath = 'app.log';

        if ($this->app->has('config')) {
            $logsPath = $this->app->get('config')->get('app.logs.path', $logsPath);
        }

        return $logsPath;
    }
}
