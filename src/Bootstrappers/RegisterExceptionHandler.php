<?php

namespace Rareloop\Lumberjack\Bootstrappers;

use Error;
use ErrorException;
use Psr\Http\Message\ResponseInterface;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Exceptions\Handler;
use Rareloop\Lumberjack\Exceptions\HandlerInterface;
use Symfony\Component\Debug\Exception\FatalErrorException;
use function Http\Response\send;

/**
 * Determine whether or not we should be in debug mode or not
 * If not debug:
 *     Register exception handlers against a `handle` function
 *     Get the handler from the container (have an object that implements an interface that the app can extend from)
 *     Call handle on the resolved object
 */

class RegisterExceptionHandler
{
    private $app;

    public function bootstrap(Application $app)
    {
        $this->app = $app;

        error_reporting(-1);
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
        register_shutdown_function([$this, 'handleShutdown']);
    }

    public function handleException($e)
    {
        if ($e instanceof Error) {
            $e = new ErrorException($e->getMessage(), 0, null, $e->getFile(), $e->getLine());
        }

        $handler = $this->getExceptionHandler();
        $handler->report($e);
        $this->send($handler->render($this->app->get('request'), $e));
    }

    public function send(ResponseInterface $response)
    {
        @send($response);
    }

    protected function getExceptionHandler() : HandlerInterface
    {
        return $this->app->get(HandlerInterface::class);
    }

    /**
     * Convert PHP errors to ErrorException instances.
     *
     * @param  int  $level
     * @param  string  $message
     * @param  string  $file
     * @param  int  $line
     * @param  array  $context
     * @return void
     *
     * @throws \ErrorException
     */
    public function handleError($level, $message, $file = '', $line = 0, $context = [])
    {
        if (error_reporting() & $level) {
            throw new ErrorException($message, 0, $level, $file, $line);
        }
    }

    public function handleShutdown()
    {
        if (! is_null($error = error_get_last()) && $this->isFatal($error['type'])) {
            $this->handleException($this->fatalExceptionFromError($error, 0));
        }
    }

    /**
     * Create a new fatal exception instance from an error array.
     *
     * @param  array  $error
     * @param  int|null  $traceOffset
     * @return \Symfony\Component\Debug\Exception\FatalErrorException
     */
    protected function fatalExceptionFromError(array $error, $traceOffset = null)
    {
        return new FatalErrorException(
            $error['message'],
            $error['type'],
            0,
            $error['file'],
            $error['line'],
            $traceOffset
        );
    }

    /**
     * Determine if the error type is fatal.
     *
     * @param  int  $type
     * @return bool
     */
    protected function isFatal($type)
    {
        return in_array($type, [E_COMPILE_ERROR, E_CORE_ERROR, E_ERROR, E_PARSE]);
    }
}
