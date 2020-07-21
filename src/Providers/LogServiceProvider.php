<?php

namespace Rareloop\Lumberjack\Providers;

use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\ErrorLogHandler;

class LogServiceProvider extends ServiceProvider
{
    public function register()
    {
        $logger = new Logger('app');

        // If the `path` config is set to false then use the Apache/Nginx error logs
        if ($this->shouldUseErrorLogHandler()) {
            $handler = new ErrorLogHandler(ErrorLogHandler::OPERATING_SYSTEM, $this->getLogLevel());
        } else {
            $handler = new StreamHandler($this->getLogsPath(), $this->getLogLevel());
        }

        $formatter = new LineFormatter(null, null, true, true);
        $formatter->includeStacktraces();
        $handler->setFormatter($formatter);

        $logger->pushHandler($handler);

        $this->app->bind('logger', $logger);
        $this->app->bind(Logger::class, $logger);
        $this->app->bind(LoggerInterface::class, $logger);
    }

    private function shouldUseErrorLogHandler()
    {
        $config = false;

        // Get the config from the container if it's been registered
        if ($this->app->has('config')) {
            $config = $this->app->get('config');
        }

        return $config && $config->get('app.logs.path') === false && $config->get('app.logs.enabled') === true;
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
            $config = $this->app->get('config');

            if (!$config->get('app.logs.enabled', false)) {
                return 'php://memory';
            }

            $logsPath = $config->get('app.logs.path', $logsPath);
        }

        return $logsPath;
    }
}
